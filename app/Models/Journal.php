<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_type',
        'reference_id',
        'date',
        'description',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }
}
