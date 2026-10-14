<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Support\Accounts;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private InventoryService $inventory,
        private JournalService $journal,
    ) {}

    /**
     * Buat produk + satuan + (opsional) saldo awal stok.
     * Saldo awal dijurnal: Persediaan (D) / Modal Pemilik (K).
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'sku' => $data['sku'],
                'name' => $data['name'],
                'base_unit_id' => $data['base_unit_id'],
                'min_stock' => $data['min_stock'] ?? 0,
                'is_active' => true,
            ]);

            $product->units()->create([
                'unit_id' => $data['base_unit_id'],
                'conversion_factor' => 1,
                'selling_price' => $data['base_selling_price'],
                'is_active' => true,
            ]);

            foreach ($data['units'] ?? [] as $row) {
                $product->units()->create([
                    'unit_id' => $row['unit_id'],
                    'conversion_factor' => $row['conversion_factor'],
                    'selling_price' => $row['selling_price'],
                    'is_active' => true,
                ]);
            }

            $openingQty = (float) ($data['opening_qty'] ?? 0);
            if ($openingQty > 0) {
                $cost = (float) $data['opening_cost'];
                $date = $data['opening_date'] ?? now()->toDateString();
                $product = $this->inventory->lockProducts([$product->id])->first();

                $this->inventory->stockIn($product, $openingQty, $cost, 'opening', $product, $date);

                $value = round($openingQty * $cost, 2);
                $this->journal->post($date, "Saldo awal persediaan {$product->sku} - {$product->name}", [
                    ['account' => Accounts::PERSEDIAAN, 'debit' => $value],
                    ['account' => Accounts::MODAL, 'credit' => $value],
                ], $product);
            }

            return $product;
        });
    }

    /**
     * Update data produk. Satuan dasar tidak bisa diubah (stok dinyatakan dalam satuan dasar).
     * Satuan yang dihapus dari form dinonaktifkan, bukan dihapus, agar histori transaksi utuh.
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update([
                'sku' => $data['sku'],
                'name' => $data['name'],
                'min_stock' => $data['min_stock'] ?? 0,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            $product->units()->where('unit_id', $product->base_unit_id)->update([
                'selling_price' => $data['base_selling_price'],
                'is_active' => true,
            ]);

            $keptIds = [];
            foreach ($data['units'] ?? [] as $row) {
                /** @var ProductUnit $unit */
                $unit = $product->units()->firstOrNew(['unit_id' => $row['unit_id']]);
                $unit->fill([
                    'conversion_factor' => $row['conversion_factor'],
                    'selling_price' => $row['selling_price'],
                    'is_active' => true,
                ])->save();
                $keptIds[] = $unit->id;
            }

            $product->units()
                ->where('unit_id', '!=', $product->base_unit_id)
                ->whereNotIn('id', $keptIds)
                ->update(['is_active' => false]);

            return $product;
        });
    }
}
