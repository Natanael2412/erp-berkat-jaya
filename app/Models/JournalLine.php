<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $fillable = ['journal_id', 'account_id', 'debit', 'credit', 'memo'];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Baris jurnal tidak boleh diubah.'));
        static::deleting(fn () => throw new \LogicException('Baris jurnal tidak boleh dihapus.'));
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
