<?php

namespace App\Services;

use App\Models\Account;
use App\Models\GoodsReceipt;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\SupplierPayment;
use App\Support\Accounts;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Siklus pembelian (inbound):
 *  1. createOrder   : PO (belum ada jurnal — PO hanya komitmen, belum transaksi akuntansi)
 *  2. receive       : Penerimaan barang -> stok & HPP naik, jurnal Persediaan (D) / Utang Usaha (K)
 *  3. returnGoods   : Retur pasca-terima -> stok turun, jurnal Utang Usaha (D) / Persediaan (K)
 *  4. pay           : Pelunasan/cicilan -> jurnal Utang Usaha (D) / Kas atau Bank (K)
 */
class PurchaseService
{
    public function __construct(
        private InventoryService $inventory,
        private JournalService $journal,
    ) {}

    public function createOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'po_number' => DocumentNumber::next('PO', $data['order_date']),
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'notes' => $data['notes'] ?? null,
                'status' => 'open',
                'created_by' => auth()->id(),
            ]);

            $units = ProductUnit::whereIn('id', array_column($data['items'], 'product_unit_id'))->get()->keyBy('id');

            foreach ($data['items'] as $item) {
                $po->items()->create([
                    'product_unit_id' => $item['product_unit_id'],
                    'conversion_factor' => $units[$item['product_unit_id']]->conversion_factor,
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $po;
        });
    }

    /**
     * Penerimaan barang (kurasi gudang). qty murni = qty_received - qty_rejected.
     * qty murni x faktor konversi = qty satuan dasar yang masuk stok.
     *
     * @param  array<int, array{qty_received: float, qty_rejected: float}>  $lines  keyed by purchase_order_item_id
     */
    public function receive(PurchaseOrder $po, string $date, array $lines, ?string $notes = null): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $date, $lines, $notes) {
            // Lock header PO: mencegah dua penerimaan bersamaan melebihi qty pesanan.
            $po = PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            $po->load(['items.receiptItems', 'items.productUnit.product']);

            if (! $po->canReceive()) {
                throw new \DomainException('PO ini tidak dalam status menunggu barang.');
            }

            $errors = [];
            $toProcess = [];
            foreach ($po->items as $item) {
                $received = round((float) ($lines[$item->id]['qty_received'] ?? 0), 4);
                $rejected = round((float) ($lines[$item->id]['qty_rejected'] ?? 0), 4);

                if ($received == 0 && $rejected == 0) {
                    continue;
                }
                if ($rejected > $received) {
                    $errors["items.{$item->id}.qty_rejected"] = 'Qty ditolak tidak boleh melebihi qty datang.';
                }
                if (($received - $rejected) - $item->qtyRemaining() > 0.00005) {
                    $errors["items.{$item->id}.qty_received"] = 'Qty diterima bersih melebihi sisa pesanan (' . $item->qtyRemaining() . ').';
                }
                $toProcess[] = [$item, $received, $rejected];
            }

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            if (! $toProcess) {
                throw ValidationException::withMessages(['items' => 'Isi minimal satu qty penerimaan.']);
            }

            $gr = GoodsReceipt::create([
                'gr_number' => DocumentNumber::next('GR', $date),
                'purchase_order_id' => $po->id,
                'receipt_date' => $date,
                'notes' => $notes,
                'received_by' => auth()->id(),
            ]);

            $products = $this->inventory->lockProducts(
                array_map(fn ($row) => $row[0]->productUnit->product_id, $toProcess)
            );

            $totalValue = 0.0;
            foreach ($toProcess as [$item, $received, $rejected]) {
                $grItem = $gr->items()->create([
                    'purchase_order_item_id' => $item->id,
                    'qty_received' => $received,
                    'qty_rejected' => $rejected,
                ]);

                $accepted = $grItem->qtyAccepted();
                if ($accepted <= 0) {
                    continue;
                }

                $baseQty = round($accepted * (float) $item->conversion_factor, 4);
                $product = $products[$item->productUnit->product_id];
                $this->inventory->stockIn($product, $baseQty, $item->baseUnitCost(), 'purchase', $gr, $date);

                $totalValue += $grItem->value($item);
            }

            $totalValue = round($totalValue, 2);
            if ($totalValue > 0) {
                $this->journal->post($date, "Penerimaan barang {$gr->gr_number} atas {$po->po_number}", [
                    ['account' => Accounts::PERSEDIAAN, 'debit' => $totalValue],
                    ['account' => Accounts::UTANG_USAHA, 'credit' => $totalValue],
                ], $gr);
            }

            $po->load('items.receiptItems');
            $po->update(['status' => $po->isFullyReceived() ? 'completed' : 'partial']);

            return $gr;
        });
    }

    /**
     * Retur barang yang sudah diterima. Persediaan dikeluarkan sebesar harga beli;
     * bila sisa stok tidak bisa menyerap selisih nilai, selisihnya ke akun Selisih Persediaan.
     *
     * @param  array<int, float>  $lines  qty retur (satuan beli) keyed by purchase_order_item_id
     */
    public function returnGoods(PurchaseOrder $po, string $date, array $lines, ?string $reason = null): PurchaseReturn
    {
        return DB::transaction(function () use ($po, $date, $lines, $reason) {
            $po = PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            $po->load([...PurchaseOrder::SUMMARY_RELATIONS, 'items.productUnit.product']);

            $errors = [];
            $toProcess = [];
            $returnValue = 0.0;
            foreach ($po->items as $item) {
                $qty = round((float) ($lines[$item->id] ?? 0), 4);
                if ($qty <= 0) {
                    continue;
                }
                if ($qty - $item->qtyReturnable() > 0.00005) {
                    $errors["items.{$item->id}"] = 'Qty retur melebihi qty yang sudah diterima (' . $item->qtyReturnable() . ').';
                }
                $toProcess[] = [$item, $qty];
                $returnValue += round($qty * (float) $item->unit_price, 2);
            }

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            if (! $toProcess) {
                throw ValidationException::withMessages(['items' => 'Isi minimal satu qty retur.']);
            }
            if ($returnValue - $po->outstandingAmount() > 0.005) {
                throw ValidationException::withMessages([
                    'items' => 'Nilai retur melebihi sisa utang PO ini. Retur atas PO yang sudah dibayar belum didukung.',
                ]);
            }

            $return = PurchaseReturn::create([
                'return_number' => DocumentNumber::next('RB', $date),
                'purchase_order_id' => $po->id,
                'return_date' => $date,
                'reason' => $reason,
                'created_by' => auth()->id(),
            ]);

            $products = $this->inventory->lockProducts(
                array_map(fn ($row) => $row[0]->productUnit->product_id, $toProcess)
            );

            $totalValue = 0.0;
            $residual = 0.0;
            foreach ($toProcess as [$item, $qty]) {
                $returnItem = $return->items()->create([
                    'purchase_order_item_id' => $item->id,
                    'qty' => $qty,
                ]);

                $product = $products[$item->productUnit->product_id];
                $baseQty = round($qty * (float) $item->conversion_factor, 4);
                $residual += $this->inventory->stockOutAtCost($product, $baseQty, $item->baseUnitCost(), 'purchase_return', $return, $date);

                $totalValue += $returnItem->value($item);
            }

            $totalValue = round($totalValue, 2);
            $residual = round($residual, 2);

            $lines = [
                ['account' => Accounts::UTANG_USAHA, 'debit' => $totalValue],
                ['account' => Accounts::PERSEDIAAN, 'credit' => $totalValue],
            ];
            if ($residual > 0) {
                $lines[] = ['account' => Accounts::SELISIH_PERSEDIAAN, 'debit' => $residual, 'memo' => 'Penyesuaian nilai sisa persediaan'];
                $lines[] = ['account' => Accounts::PERSEDIAAN, 'credit' => $residual, 'memo' => 'Penyesuaian nilai sisa persediaan'];
            } elseif ($residual < 0) {
                $lines[] = ['account' => Accounts::PERSEDIAAN, 'debit' => -$residual, 'memo' => 'Penyesuaian nilai sisa persediaan'];
                $lines[] = ['account' => Accounts::SELISIH_PERSEDIAAN, 'credit' => -$residual, 'memo' => 'Penyesuaian nilai sisa persediaan'];
            }

            $this->journal->post($date, "Retur pembelian {$return->return_number} atas {$po->po_number}", $lines, $return);

            return $return;
        });
    }

    public function pay(PurchaseOrder $po, string $date, float $amount, int $accountId, ?string $notes = null): SupplierPayment
    {
        return DB::transaction(function () use ($po, $date, $amount, $accountId, $notes) {
            $po = PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            $po->load(PurchaseOrder::SUMMARY_RELATIONS);

            $amount = round($amount, 2);
            $outstanding = $po->outstandingAmount();
            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Nominal pembayaran harus lebih dari 0.']);
            }
            if ($amount - $outstanding > 0.005) {
                throw ValidationException::withMessages(['amount' => 'Nominal melebihi sisa utang (Rp ' . number_format($outstanding, 0, ',', '.') . ').']);
            }

            $account = Account::whereKey($accountId)->where('is_cash', true)->lockForUpdate()->firstOrFail();
            $balance = $account->balance();
            if ($amount - $balance > 0.005) {
                throw ValidationException::withMessages([
                    'account_id' => "Saldo {$account->name} tidak cukup (Rp " . number_format($balance, 0, ',', '.') . ').',
                ]);
            }

            $payment = SupplierPayment::create([
                'payment_number' => DocumentNumber::next('BYR', $date),
                'purchase_order_id' => $po->id,
                'payment_date' => $date,
                'amount' => $amount,
                'account_id' => $account->id,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            $this->journal->post($date, "Pembayaran utang {$po->po_number} ({$payment->payment_number})", [
                ['account' => Accounts::UTANG_USAHA, 'debit' => $amount],
                ['account' => $account->code, 'credit' => $amount],
            ], $payment);

            return $payment;
        });
    }

    public function cancel(PurchaseOrder $po): void
    {
        DB::transaction(function () use ($po) {
            $po = PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            if (! $po->canCancel()) {
                throw new \DomainException('PO hanya bisa dibatalkan bila belum ada penerimaan barang.');
            }
            $po->update(['status' => 'cancelled']);
        });
    }

    /** Tutup PO walau belum semua barang datang (supplier tidak sanggup memenuhi sisa). */
    public function markAsComplete(PurchaseOrder $po): void
    {
        DB::transaction(function () use ($po) {
            $po = PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            if ($po->status !== 'partial') {
                throw new \DomainException('Hanya PO yang diterima sebagian yang bisa ditutup manual.');
            }
            $po->update(['status' => 'completed']);
        });
    }
}
