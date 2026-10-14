<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = ['gr_number', 'purchase_order_id', 'receipt_date', 'notes', 'received_by'];

    protected $casts = ['receipt_date' => \App\Casts\DateOnly::class];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'source');
    }
}
