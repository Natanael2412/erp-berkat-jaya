<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['code', 'name', 'type', 'normal_balance', 'is_cash'];

    protected $casts = ['is_cash' => 'boolean'];

    public const TYPES = [
        'asset' => 'Aset',
        'liability' => 'Liabilitas',
        'equity' => 'Ekuitas',
        'revenue' => 'Pendapatan',
        'expense' => 'Beban',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /** Saldo sesuai saldo normal akun (positif = sesuai saldo normal). */
    public function balance(?string $until = null, ?string $from = null): float
    {
        $query = $this->lines()->join('journals', 'journals.id', '=', 'journal_lines.journal_id');
        if ($from) {
            $query->where('journals.journal_date', '>=', $from);
        }
        if ($until) {
            $query->where('journals.journal_date', '<=', $until);
        }
        $row = $query->selectRaw('COALESCE(SUM(debit),0) d, COALESCE(SUM(credit),0) c')->first();

        $net = (float) $row->d - (float) $row->c;

        return round($this->normal_balance === 'debit' ? $net : -$net, 2);
    }
}
