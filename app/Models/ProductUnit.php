<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = ['product_id', 'unit_id', 'conversion_factor', 'selling_price', 'is_active'];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function label(): string
    {
        return $this->product->name . ' / ' . $this->unit->name;
    }
}
