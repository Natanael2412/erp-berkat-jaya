<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jurnal bersifat immutable. Koreksi dilakukan lewat JournalService::reverse().
 */
class Journal extends Model
{
    protected $fillable = [
        'journal_number',
        'journal_date',
        'description',
        'source_type',
        'source_id',
        'reversal_of_id',
        'created_by',
    ];

    protected $casts = ['journal_date' => \App\Casts\DateOnly::class];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Jurnal tidak boleh diubah. Gunakan jurnal pembalik.'));
        static::deleting(fn () => throw new \LogicException('Jurnal tidak boleh dihapus. Gunakan jurnal pembalik.'));
    }

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function reversalOf()
    {
        return $this->belongsTo(Journal::class, 'reversal_of_id');
    }

    public function reversedBy()
    {
        return $this->hasOne(Journal::class, 'reversal_of_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalDebit(): float
    {
        return round((float) $this->lines->sum('debit'), 2);
    }

    public function totalCredit(): float
    {
        return round((float) $this->lines->sum('credit'), 2);
    }
}
