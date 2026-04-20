<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestSession extends Model
{
    protected $fillable = [
        'applicant_id', 'test_id', 'login', 'password',
        'score', 'correct_answers', 'status',
        'started_at', 'finished_at', 'expires_at', 'answers',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'answers'     => 'array',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
            'expires_at'  => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
