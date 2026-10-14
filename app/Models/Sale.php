<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'sale_date',
        'cashier_id',
        'payment_method',
        'amount_paid',
        'status',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'voided_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function voider()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function journals()
    {
        return $this->morphMany(Journal::class, 'source');
    }

    /** Tambahkan kolom virtual `total_amount` = SUM(qty * unit_price) tanpa menyimpannya. */
    public function scopeWithTotal(Builder $query): Builder
    {
        return $query->addSelect([
            'total_amount' => SaleItem::selectRaw('COALESCE(SUM(ROUND(qty * unit_price, 2)), 0)')
                ->whereColumn('sale_items.sale_id', 'sales.id'),
        ]);
    }

    public function total(): float
    {
        if (array_key_exists('total_amount', $this->attributes)) {
            return round((float) $this->attributes['total_amount'], 2);
        }

        return round($this->items->sum(fn ($i) => $i->subtotal()), 2);
    }

    public function change(): float
    {
        return max(0, round((float) $this->amount_paid - $this->total(), 2));
    }

    public function cogs(): float
    {
        return round($this->items->sum(fn ($i) => $i->cogs()), 2);
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }
}
