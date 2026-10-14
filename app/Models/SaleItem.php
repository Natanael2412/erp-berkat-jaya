<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'product_unit_id', 'conversion_factor', 'qty', 'unit_price', 'hpp_at_sale'];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'qty' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'hpp_at_sale' => 'decimal:4',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function subtotal(): float
    {
        return round((float) $this->qty * (float) $this->unit_price, 2);
    }

    public function baseQty(): float
    {
        return round((float) $this->qty * (float) $this->conversion_factor, 4);
    }

    /** HPP item = qty dasar x HPP per satuan dasar saat transaksi. */
    public function cogs(): float
    {
        return round($this->baseQty() * (float) $this->hpp_at_sale, 2);
    }
}
