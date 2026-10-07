<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'account_code',
        'debit_amount',
        'credit_amount',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_code', 'code');
    }
}
