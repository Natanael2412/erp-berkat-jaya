<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Mesin persediaan: metode HPP Rata-rata Bergerak (Moving Average) + kartu stok + manajemen batch rol.
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
     * Generate nomor SKU Batch unik untuk satu rol fisik (misal: KBL-NYM-2X15-ROL001).
     */
    public function nextBatchSku(Product $product): string
    {
        $prefix = $product->sku . '-ROL';
        $count = ProductBatch::where('product_id', $product->id)->count();
        $seq = $count + 1;

        while (ProductBatch::where('batch_sku', sprintf('%s%03d', $prefix, $seq))->exists()) {
            $seq++;
        }

        return sprintf('%s%03d', $prefix, $seq);
    }

    /**
     * Buat entitas stok rol fisik terpisah (ProductBatch) dan catat mutasi masuk per rol.
     */
    public function createRollBatch(
        Product $product,
        int $qtyBase,
        float $unitCost,
        string $type,
        ?Model $source,
        CarbonInterface|string $date,
        ?int $goodsReceiptId = null,
        ?int $poItemId = null,
    ): ProductBatch {
        $batch = ProductBatch::create([
            'product_id' => $product->id,
            'goods_receipt_id' => $goodsReceiptId,
            'purchase_order_item_id' => $poItemId,
            'batch_sku' => $this->nextBatchSku($product),
            'initial_qty' => $qtyBase,
            'remaining_qty' => $qtyBase,
            'unit_cost' => round($unitCost, 4),
            'received_date' => $date instanceof CarbonInterface ? $date->toDateString() : substr((string) $date, 0, 10),
            'is_active' => true,
        ]);

        $this->stockIn($product, $qtyBase, $unitCost, $type, $source, $date, $batch->id);

        return $batch;
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
        ?int $batchId = null,
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

        return $this->apply($product, $newQty, $newHpp, $type, $source, $date, $qtyBase, 0, $unitCost, $batchId);
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
        ?int $batchId = null,
    ): float {
        $this->assertAvailable($product, $qtyBase);

        $hpp = (float) $product->current_hpp;
        $newQty = round((float) $product->current_stock - $qtyBase, 4);

        $this->apply($product, $newQty, $hpp, $type, $source, $date, 0, $qtyBase, $hpp, $batchId);

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
        ?int $batchId = null,
    ): float {
        $this->assertAvailable($product, $qtyBase);

        $oldQty = (float) $product->current_stock;
        $remainingValue = ($oldQty * (float) $product->current_hpp) - ($qtyBase * $unitCost);
        $newQty = round($oldQty - $qtyBase, 4);

        $targetValue = $newQty > 0 ? max(0, $remainingValue) : 0;
        $newHpp = $newQty > 0 ? round($targetValue / $newQty, 4) : 0;
        $residual = round($remainingValue - $targetValue, 2);

        $this->apply($product, $newQty, $newHpp, $type, $source, $date, 0, $qtyBase, $unitCost, $batchId);

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
        ?int $batchId = null,
    ): StockMovement {
        $product->forceFill([
            'current_stock' => $newQty,
            'current_hpp' => $newHpp,
        ])->save();

        return StockMovement::create([
            'product_id' => $product->id,
            'product_batch_id' => $batchId,
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
