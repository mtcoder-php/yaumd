<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectionSubject extends Model
{
    protected $fillable = [
        'direction_id', 'subject_id', 'block_type',
        'questions_count', 'score_per_question', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'          => 'boolean',
            'score_per_question' => 'decimal:1',
        ];
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
