<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XapiStatement extends Model
{
    protected $fillable = [
        'user_id', 'lesson_id', 'statement_id',
        'verb', 'object_id', 'object_type',
        'actor', 'result', 'context', 'raw', 'stored_at',
    ];

    protected function casts(): array
    {
        return [
            'actor'     => 'array',
            'result'    => 'array',
            'context'   => 'array',
            'raw'       => 'array',
            'stored_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
