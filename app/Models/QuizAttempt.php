<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'user_id', 'attempt',
        'score', 'max_score', 'percentage',
        'is_passed', 'time_spent', 'status',
        'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'is_passed'   => 'boolean',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
            'score'       => 'decimal:2',
            'max_score'   => 'decimal:2',
            'percentage'  => 'decimal:2',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }
}
