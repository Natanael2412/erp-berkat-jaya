<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['code', 'name', 'allows_decimal'];

    protected $casts = [
        'allows_decimal' => 'boolean',
    ];

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function allowsDecimal(): bool
    {
        return false;
    }
}
