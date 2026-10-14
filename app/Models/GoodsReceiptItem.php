<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = ['goods_receipt_id', 'purchase_order_item_id', 'qty_received', 'qty_rejected'];

    protected $casts = [
        'qty_received' => 'decimal:4',
        'qty_rejected' => 'decimal:4',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function qtyAccepted(): float
    {
        return round((float) $this->qty_received - (float) $this->qty_rejected, 4);
    }

    /** Nilai barang diterima bersih (dasar jurnal Persediaan/Utang). */
    public function value(?PurchaseOrderItem $poItem = null): float
    {
        $poItem ??= $this->purchaseOrderItem;

        return round($this->qtyAccepted() * (float) $poItem->unit_price, 2);
    }
}
