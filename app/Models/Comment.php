<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lesson_id', 'user_id', 'parent_id',
        'body', 'likes', 'is_pinned',
        'is_answer', 'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned'   => 'boolean',
            'is_answer'   => 'boolean',
            'is_approved' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }
}
