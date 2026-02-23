<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'nama',
        'nis',
        'kelas',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    /**
     * Get the transactions for the student.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
