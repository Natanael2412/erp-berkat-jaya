<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'base_unit',
        'current_stock',
        'current_hpp',
    ];

    public function uomConversions()
    {
        return $this->hasMany(ProductUomConversion::class);
    }
}
