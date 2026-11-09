<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Support\Accounts;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Siklus penjualan (outbound / POS).
 *
 * Jurnal penjualan:
 *  1. Kas/Bank (D)  / Penjualan (K)   sebesar total harga jual
 *  2. HPP (D)       / Persediaan (K)  sebesar SUM(qty dasar x hpp_at_sale)
 */
class SalesService
{
    public function __construct(
        private InventoryService $inventory,
        private JournalService $journal,
    ) {}

    /**
     * @param  array<int, array{product_unit_id: int, qty: int|float, product_batch_id?: int|null}>  $cart
     */
    public function checkout(array $cart, string $paymentMethod, float $amountPaid): Sale
    {
        return DB::transaction(function () use ($cart, $paymentMethod, $amountPaid) {
            // Harga diambil dari database, BUKAN dari browser.
            $units = ProductUnit::with('product.baseUnit', 'unit')
                ->whereIn('id', array_column($cart, 'product_unit_id'))
                ->get()->keyBy('id');

            $lines = [];
            foreach ($cart as $row) {
                $unit = $units[$row['product_unit_id']] ?? null;
                if (! $unit || ! $unit->is_active || ! $unit->product->is_active) {
                    throw ValidationException::withMessages(['cart' => 'Ada produk/satuan yang tidak aktif. Muat ulang halaman POS.']);
                }

                $rawQty = (float) $row['qty'];
                if ($rawQty <= 0 || abs($rawQty - round($rawQty)) > 0.00005) {
                    throw ValidationException::withMessages([
                        'cart' => "Kuantitas {$unit->unit->name} ({$unit->product->name}) tidak boleh berupa pecahan/desimal. Harus bilangan bulat (Integer).",
                    ]);
                }

                $qty = (int) round($rawQty);
                $lines[] = [
                    'unit' => $unit,
                    'qty' => $qty,
                    'batch_id' => ! empty($row['product_batch_id']) ? (int) $row['product_batch_id'] : null,
                ];
            }

            // Pessimistic locking: SELECT ... FOR UPDATE pada semua produk di keranjang.
            $products = $this->inventory->lockProducts(array_map(fn ($l) => $l['unit']->product_id, $lines));
            $products->load(['baseUnit', 'units.unit']);

            // Validasi stok total per produk (satu produk bisa muncul dengan beberapa satuan).
            $needed = [];
            foreach ($lines as $l) {
                $pid = $l['unit']->product_id;
                $needed[$pid] = ($needed[$pid] ?? 0) + $l['qty'] * (int) round((float) $l['unit']->conversion_factor);
            }
            foreach ($needed as $pid => $qtyBase) {
                $p = $products[$pid];
                if ($qtyBase - (float) $p->current_stock > 0.00005) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$p->name} tidak mencukupi. Tersedia " . rtrim(rtrim(number_format((float) $p->current_stock, 4, ',', '.'), '0'), ',') . " {$p->baseUnit?->name}.",
                    ]);
                }
            }

            // Lock dan alokasikan stok rol fisik (ProductBatch) untuk produk kabel/rol.
            $batchesByProduct = ProductBatch::whereIn('product_id', $products->keys())
                ->where('is_active', true)
                ->where('remaining_qty', '>', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->groupBy('product_id');

            $resolvedLines = [];
            foreach ($lines as $l) {
                $unit = $l['unit'];
                $product = $products[$unit->product_id];
                $factor = (int) round((float) $unit->conversion_factor);
                $baseQty = $l['qty'] * max(1, $factor);
                $unitCode = strtoupper($unit->unit->code ?? '');
                $productBatches = $batchesByProduct->get($product->id, collect());

                if ($product->hasRollUnit() && $productBatches->isNotEmpty()) {
                    if ($l['batch_id']) {
                        /** @var ProductBatch|null $batch */
                        $batch = $productBatches->firstWhere('id', $l['batch_id']);
                        if (! $batch) {
                            throw ValidationException::withMessages([
                                'cart' => "Rol/Batch yang dipilih untuk {$product->name} tidak ditemukan atau sudah habis.",
                            ]);
                        }
                        if ($batch->remaining_qty < $baseQty) {
                            throw ValidationException::withMessages([
                                'cart' => "Stok pada rol {$batch->batch_sku} ({$product->name}) tidak mencukupi. Sisa pada rol ini: {$batch->remaining_qty} {$product->baseUnit?->name}, diminta: {$baseQty} {$product->baseUnit?->name}. Tidak boleh menggabungkan potongan lintas rol.",
                            ]);
                        }
                        $batch->remaining_qty -= $baseQty;
                        $resolvedLines[] = ['unit' => $unit, 'qty' => $l['qty'], 'batch' => $batch];
                    } elseif ($unitCode === 'ROLL' && $l['qty'] > 1) {
                        // Pembelian beberapa rol utuh tanpa memilih batch spesifik: alokasikan 1 rol utuh per baris
                        for ($i = 0; $i < $l['qty']; $i++) {
                            $batch = $productBatches->first(fn (ProductBatch $b) => $b->remaining_qty >= $factor);
                            if (! $batch) {
                                throw ValidationException::withMessages([
                                    'cart' => "Tidak ada rol fisik {$product->name} yang memiliki stok utuh {$factor} {$product->baseUnit?->name} untuk memenuhi {$l['qty']} Roll.",
                                ]);
                            }
                            $batch->remaining_qty -= $factor;
                            $resolvedLines[] = ['unit' => $unit, 'qty' => 1, 'batch' => $batch];
                        }
                    } else {
                        // Cari satu rol fisik yang memiliki sisa stok cukup untuk memotong seluruh $baseQty secara utuh
                        $batch = $productBatches->first(fn (ProductBatch $b) => $b->remaining_qty >= $baseQty);
                        if (! $batch) {
                            $maxRoll = (int) $productBatches->max('remaining_qty');
                            throw ValidationException::withMessages([
                                'cart' => "Tidak ada satu rol tunggal {$product->name} yang mencukupi {$baseQty} {$product->baseUnit?->name} (sisa rol terbesar: {$maxRoll} {$product->baseUnit?->name}). Pilih rol spesifik di POS.",
                            ]);
                        }
                        $batch->remaining_qty -= $baseQty;
                        $resolvedLines[] = ['unit' => $unit, 'qty' => $l['qty'], 'batch' => $batch];
                    }
                } else {
                    $resolvedLines[] = ['unit' => $unit, 'qty' => $l['qty'], 'batch' => null];
                }
            }

            $total = round(array_sum(array_map(fn ($l) => round($l['qty'] * (float) $l['unit']->selling_price, 2), $resolvedLines)), 2);
            if ($total <= 0) {
                throw ValidationException::withMessages(['cart' => 'Total transaksi harus lebih dari 0. Periksa harga jual produk.']);
            }

            if ($paymentMethod === 'cash') {
                if ($amountPaid + 0.005 < $total) {
                    throw ValidationException::withMessages(['amount_paid' => 'Uang diterima kurang dari total belanja.']);
                }
            } else {
                $amountPaid = $total;
            }

            $now = now();
            $sale = Sale::create([
                'invoice_number' => DocumentNumber::next('INV', $now),
                'sale_date' => $now,
                'cashier_id' => auth()->id(),
                'payment_method' => $paymentMethod,
                'amount_paid' => round($amountPaid, 2),
                'status' => 'completed',
            ]);

            $cogs = 0.0;
            foreach ($resolvedLines as $l) {
                $unit = $l['unit'];
                $product = $products[$unit->product_id];
                /** @var ProductBatch|null $batch */
                $batch = $l['batch'];
                $baseQty = round($l['qty'] * (float) $unit->conversion_factor, 4);

                if ($batch) {
                    $batch->save();
                }

                $hpp = $this->inventory->stockOutAtAverage($product, $baseQty, 'sale', $sale, $now, $batch?->id);

                $item = $sale->items()->create([
                    'product_unit_id' => $unit->id,
                    'product_batch_id' => $batch?->id,
                    'conversion_factor' => $unit->conversion_factor,
                    'qty' => $l['qty'],
                    'unit_price' => $unit->selling_price,
                    'hpp_at_sale' => $hpp,
                ]);
                $cogs += $item->cogs();
            }
            $cogs = round($cogs, 2);

            $cashAccount = Accounts::forPaymentMethod($paymentMethod);
            $journalLines = [
                ['account' => $cashAccount, 'debit' => $total, 'memo' => 'Penerimaan penjualan'],
                ['account' => Accounts::PENJUALAN, 'credit' => $total, 'memo' => 'Pendapatan penjualan'],
                ['account' => Accounts::HPP, 'debit' => $cogs, 'memo' => 'Harga pokok penjualan'],
                ['account' => Accounts::PERSEDIAAN, 'credit' => $cogs, 'memo' => 'Pengurangan persediaan'],
            ];
            $this->journal->post($now, "Penjualan {$sale->invoice_number} ({$sale->paymentLabel()})", $journalLines, $sale);

            return $sale;
        });
    }

    /** Batalkan penjualan: stok kembali sebesar hpp_at_sale dan jurnal dibalik. */
    public function void(Sale $sale, string $reason): Sale
    {
        return DB::transaction(function () use ($sale, $reason) {
            $sale = Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();
            if ($sale->isVoid()) {
                throw new \DomainException('Transaksi ini sudah dibatalkan.');
            }

            $sale->load('items.productUnit', 'items.productBatch');
            $products = $this->inventory->lockProducts($sale->items->map(fn ($i) => $i->productUnit->product_id)->all());
            $now = now();

            foreach ($sale->items as $item) {
                $baseQty = (int) round($item->baseQty());
                if ($item->product_batch_id) {
                    $batch = ProductBatch::whereKey($item->product_batch_id)->lockForUpdate()->first();
                    if ($batch) {
                        $batch->increment('remaining_qty', $baseQty);
                    }
                }

                $this->inventory->stockIn(
                    $products[$item->productUnit->product_id],
                    $item->baseQty(),
                    (float) $item->hpp_at_sale,
                    'sale_void',
                    $sale,
                    $now,
                    $item->product_batch_id,
                );
            }

            $original = $sale->journals()->whereNull('reversal_of_id')->firstOrFail();
            $this->journal->reverse($original, $now, "Batal penjualan {$sale->invoice_number}: {$reason}", $sale);

            $sale->update([
                'status' => 'void',
                'voided_at' => $now,
                'voided_by' => auth()->id(),
                'void_reason' => $reason,
            ]);

            return $sale;
        });
    }
}
