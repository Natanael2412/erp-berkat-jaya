<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Nilai turunan (qty diterima, utang, sisa utang, status bayar) dihitung dari dokumen
 * GR, retur, dan pembayaran — tidak disimpan di tabel ini.
 */
class PurchaseOrder extends Model
{
    protected $fillable = ['po_number', 'supplier_id', 'order_date', 'status', 'notes', 'created_by'];

    protected $casts = ['order_date' => \App\Casts\DateOnly::class];

    public const STATUSES = [
        'open' => 'Menunggu Barang',
        'partial' => 'Diterima Sebagian',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const PAYMENT_STATUSES = [
        'none' => 'Belum Ada Tagihan',
        'unpaid' => 'Belum Dibayar',
        'partial' => 'Dicicil',
        'paid' => 'Lunas',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /** Relasi yang dibutuhkan untuk menghitung semua nilai turunan. */
    public const SUMMARY_RELATIONS = [
        'items.receiptItems',
        'items.returnItems',
        'payments',
    ];

    public function orderedAmount(): float
    {
        return round($this->items->sum(fn ($i) => round((float) $i->qty_ordered * (float) $i->unit_price, 2)), 2);
    }

    /** Utang yang diakui = nilai barang diterima bersih - nilai retur. */
    public function payableAmount(): float
    {
        return round($this->items->sum(fn ($i) => $i->receivedValue() - $i->returnedValue()), 2);
    }

    public function paidAmount(): float
    {
        return round((float) $this->payments->sum('amount'), 2);
    }

    public function outstandingAmount(): float
    {
        return round($this->payableAmount() - $this->paidAmount(), 2);
    }

    public function paymentStatus(): string
    {
        $payable = $this->payableAmount();
        $paid = $this->paidAmount();

        return match (true) {
            $payable <= 0 && $paid <= 0 => 'none',
            $paid <= 0 => 'unpaid',
            $paid < $payable => 'partial',
            default => 'paid',
        };
    }

    public function isFullyReceived(): bool
    {
        return $this->items->every(fn ($i) => $i->qtyRemaining() <= 0);
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['open', 'partial'], true);
    }

    public function canCancel(): bool
    {
        return $this->status === 'open' && $this->receipts()->doesntExist();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
