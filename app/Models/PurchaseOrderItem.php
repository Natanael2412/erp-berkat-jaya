<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'product_unit_id', 'conversion_factor', 'qty_ordered', 'unit_price'];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'qty_ordered' => 'decimal:4',
        'unit_price' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function receiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /** Qty diterima bersih (datang - ditolak), dalam satuan beli. */
    public function qtyAccepted(): float
    {
        return round($this->receiptItems->sum(fn ($r) => (float) $r->qty_received - (float) $r->qty_rejected), 4);
    }

    public function qtyRejected(): float
    {
        return round((float) $this->receiptItems->sum('qty_rejected'), 4);
    }

    public function qtyReturned(): float
    {
        return round((float) $this->returnItems->sum('qty'), 4);
    }

    public function qtyRemaining(): float
    {
        return max(0, round((float) $this->qty_ordered - $this->qtyAccepted(), 4));
    }

    /** Qty yang masih bisa diretur ke supplier. */
    public function qtyReturnable(): float
    {
        return max(0, round($this->qtyAccepted() - $this->qtyReturned(), 4));
    }

    public function receivedValue(): float
    {
        return round($this->receiptItems->sum(fn ($r) => $r->value($this)), 2);
    }

    public function returnedValue(): float
    {
        return round($this->returnItems->sum(fn ($r) => $r->value($this)), 2);
    }

    /** Biaya per satuan dasar. */
    public function baseUnitCost(): float
    {
        return round((float) $this->unit_price / (float) $this->conversion_factor, 4);
    }
}
