<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAttachment extends Model
{
    protected $fillable = [
        'lesson_id', 'title', 'path', 'mime_type',
        'file_size', 'type', 'is_downloadable', 'order',
    ];

    protected function casts(): array
    {
        return ['is_downloadable' => 'boolean'];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
