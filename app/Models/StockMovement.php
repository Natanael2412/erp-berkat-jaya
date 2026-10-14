<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Kartu stok: satu baris untuk setiap mutasi stok, dengan saldo berjalan. */
class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'movement_date',
        'type',
        'source_type',
        'source_id',
        'qty_in',
        'qty_out',
        'unit_cost',
        'balance_qty',
        'balance_hpp',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'qty_in' => 'decimal:4',
        'qty_out' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'balance_qty' => 'decimal:4',
        'balance_hpp' => 'decimal:4',
    ];

    public const TYPES = [
        'opening' => 'Saldo Awal',
        'purchase' => 'Pembelian',
        'purchase_return' => 'Retur Pembelian',
        'sale' => 'Penjualan',
        'sale_void' => 'Batal Penjualan',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
