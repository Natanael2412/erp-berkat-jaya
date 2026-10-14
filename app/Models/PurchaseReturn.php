<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = ['return_number', 'purchase_order_id', 'return_date', 'reason', 'created_by'];

    protected $casts = ['return_date' => \App\Casts\DateOnly::class];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'source');
    }
}
