<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id', 'user_id', 'attempt',
        'text_answer', 'files', 'status',
        'score', 'is_late', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'files'        => 'array',
            'is_late'      => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(SubmissionReview::class);
    }
}
