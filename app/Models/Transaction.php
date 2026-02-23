<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'student_id',
        'amount',
        'type',
        'description',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
