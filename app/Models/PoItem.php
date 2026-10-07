<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_id',
        'product_id',
        'uom_id',
        'qty_ordered',
        'qty_received',
        'qty_retur',
        'unit_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function uom()
    {
        return $this->belongsTo(ProductUomConversion::class, 'uom_id');
    }
}
