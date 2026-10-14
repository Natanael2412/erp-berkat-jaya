<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $fillable = ['purchase_return_id', 'purchase_order_item_id', 'qty'];

    protected $casts = ['qty' => 'decimal:4'];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function value(?PurchaseOrderItem $poItem = null): float
    {
        $poItem ??= $this->purchaseOrderItem;

        return round((float) $this->qty * (float) $poItem->unit_price, 2);
    }
}
