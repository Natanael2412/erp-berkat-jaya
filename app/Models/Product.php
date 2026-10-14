<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'base_unit_id',
        'min_stock',
        'current_stock',
        'current_hpp',
        'is_active',
    ];

    protected $casts = [
        'min_stock' => 'decimal:4',
        'current_stock' => 'decimal:4',
        'current_hpp' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /** Semua satuan (termasuk satuan dasar dengan faktor 1). */
    public function units()
    {
        return $this->hasMany(ProductUnit::class)->orderBy('conversion_factor');
    }

    public function activeUnits()
    {
        return $this->units()->where('is_active', true);
    }

    /** Baris product_units untuk satuan dasar (faktor = 1). */
    public function baseProductUnit(): ?ProductUnit
    {
        return $this->units->firstWhere('unit_id', $this->base_unit_id);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= (float) $this->min_stock;
    }

    public function stockValue(): float
    {
        return round((float) $this->current_stock * (float) $this->current_hpp, 2);
    }
}
