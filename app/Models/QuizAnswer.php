<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    protected $fillable = [
        'attempt_id', 'question_id',
        'selected_options', 'text_answer',
        'is_correct', 'score',
    ];

    protected function casts(): array
    {
        return [
            'selected_options' => 'array',
            'is_correct'       => 'boolean',
            'score'            => 'decimal:2',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class);
    }
}
