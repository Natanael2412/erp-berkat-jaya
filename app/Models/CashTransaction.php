<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = ['transaction_number', 'transaction_date', 'type', 'cash_account_id', 'amount', 'description', 'created_by'];

    protected $casts = [
        'transaction_date' => \App\Casts\DateOnly::class,
        'amount' => 'decimal:2',
    ];

    public const TYPES = [
        'capital' => 'Setoran Modal',
        'drawing' => 'Prive (Ambil Pribadi)',
        'expense' => 'Beban Operasional',
    ];

    public function cashAccount()
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'source');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isInflow(): bool
    {
        return $this->type === 'capital';
    }
}
