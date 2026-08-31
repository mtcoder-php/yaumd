<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonVideo extends Model
{
    protected $fillable = [
        'lesson_id', 'source', 'url',
        'path_360', 'path_720', 'path_1080',
        'path_2k', 'path_4k', 'path_8k',
        'thumbnail', 'duration', 'file_size', 'is_processed',
    ];

    protected function casts(): array
    {
        return ['is_processed' => 'boolean'];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
