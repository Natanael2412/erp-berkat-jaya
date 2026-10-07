<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'status',
        'total_amount',
    ];

    public function items()
    {
        return $this->hasMany(PoItem::class, 'po_id');
    }
}
