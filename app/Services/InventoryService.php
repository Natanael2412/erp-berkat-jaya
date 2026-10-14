<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Mesin persediaan: metode HPP Rata-rata Bergerak (Moving Average) + kartu stok.
 *
 * Semua method mengasumsikan baris produk SUDAH di-lock lewat lockProducts()
 * di dalam DB::transaction() (pessimistic locking).
 */
class InventoryService
{
    /**
     * Lock baris produk (SELECT ... FOR UPDATE) dengan urutan id menaik
     * agar dua transaksi yang mengunci produk sama tidak saling deadlock.
     *
     * @param  array<int>  $productIds
     * @return Collection<int, Product>
     */
    public function lockProducts(array $productIds): Collection
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('lockProducts() harus dipanggil di dalam transaksi database.');
        }

        $ids = array_values(array_unique($productIds));
        sort($ids);

        return Product::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
    }

    /**
     * Barang masuk. HPP baru = ((stok lama x HPP lama) + (qty masuk x biaya)) / (stok lama + qty masuk).
     */
    public function stockIn(
        Product $product,
        float $qtyBase,
        float $unitCost,
        string $type,
        ?Model $source,
        CarbonInterface|string $date,
    ): StockMovement {
        if ($qtyBase <= 0) {
            throw new \DomainException('Qty masuk harus lebih dari 0.');
        }

        $oldQty = (float) $product->current_stock;
        $oldHpp = (float) $product->current_hpp;
        $newQty = round($oldQty + $qtyBase, 4);

        $newHpp = $newQty > 0
            ? round((($oldQty * $oldHpp) + ($qtyBase * $unitCost)) / $newQty, 4)
            : round($unitCost, 4);

        return $this->apply($product, $newQty, $newHpp, $type, $source, $date, $qtyBase, 0, $unitCost);
    }

    /**
     * Barang keluar karena penjualan: dibebankan sebesar HPP saat ini. HPP tidak berubah.
     *
     * @return float HPP per satuan dasar yang dipakai
     */
    public function stockOutAtAverage(
        Product $product,
        float $qtyBase,
        string $type,
        ?Model $source,
        CarbonInterface|string $date,
    ): float {
        $this->assertAvailable($product, $qtyBase);

        $hpp = (float) $product->current_hpp;
        $newQty = round((float) $product->current_stock - $qtyBase, 4);

        $this->apply($product, $newQty, $hpp, $type, $source, $date, 0, $qtyBase, $hpp);

        return $hpp;
    }

    /**
     * Barang keluar dengan biaya tertentu (retur pembelian: dikeluarkan sebesar harga beli).
     * HPP sisa dihitung ulang: (nilai lama - nilai keluar) / qty sisa.
     *
     * @return float Selisih nilai yang tidak bisa diserap sisa stok (positif = rugi selisih),
     *               harus dijurnal ke akun Selisih Persediaan agar buku besar = kartu stok.
     */
    public function stockOutAtCost(
        Product $product,
        float $qtyBase,
        float $unitCost,
        string $type,
        ?Model $source,
        CarbonInterface|string $date,
    ): float {
        $this->assertAvailable($product, $qtyBase);

        $oldQty = (float) $product->current_stock;
        $remainingValue = ($oldQty * (float) $product->current_hpp) - ($qtyBase * $unitCost);
        $newQty = round($oldQty - $qtyBase, 4);

        $targetValue = $newQty > 0 ? max(0, $remainingValue) : 0;
        $newHpp = $newQty > 0 ? round($targetValue / $newQty, 4) : 0;
        $residual = round($remainingValue - $targetValue, 2);

        $this->apply($product, $newQty, $newHpp, $type, $source, $date, 0, $qtyBase, $unitCost);

        return abs($residual) < 0.01 ? 0.0 : $residual;
    }

    private function assertAvailable(Product $product, float $qtyBase): void
    {
        if ($qtyBase <= 0) {
            throw new \DomainException('Qty keluar harus lebih dari 0.');
        }
        if ($qtyBase - (float) $product->current_stock > 0.00005) {
            throw new \DomainException(sprintf(
                'Stok %s tidak mencukupi (tersedia %s, diminta %s).',
                $product->name,
                rtrim(rtrim(number_format((float) $product->current_stock, 4, ',', '.'), '0'), ','),
                rtrim(rtrim(number_format($qtyBase, 4, ',', '.'), '0'), ','),
            ));
        }
    }

    private function apply(
        Product $product,
        float $newQty,
        float $newHpp,
        string $type,
        ?Model $source,
        CarbonInterface|string $date,
        float $qtyIn,
        float $qtyOut,
        float $unitCost,
    ): StockMovement {
        $product->forceFill([
            'current_stock' => $newQty,
            'current_hpp' => $newHpp,
        ])->save();

        return StockMovement::create([
            'product_id' => $product->id,
            'movement_date' => $date instanceof CarbonInterface ? $date : Carbon::parse($date)->setTimeFrom(now()),
            'type' => $type,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'unit_cost' => round($unitCost, 4),
            'balance_qty' => $newQty,
            'balance_hpp' => $newHpp,
            'created_by' => auth()->id(),
        ]);
    }
}
