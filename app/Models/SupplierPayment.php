<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = ['payment_number', 'purchase_order_id', 'payment_date', 'amount', 'account_id', 'notes', 'created_by'];

    protected $casts = [
        'payment_date' => \App\Casts\DateOnly::class,
        'amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
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
