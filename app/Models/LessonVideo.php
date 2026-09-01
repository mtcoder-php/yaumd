<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LessonVideo extends Model
{
    protected $appends = ['video_url'];

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

    // Hozircha ko'p sifatli transkodlash yo'q — yuklangan video "path_720"
    // ustunida saqlanadi (bitta original fayl sifatida). YouTube/Vimeo bo'lsa
    // to'g'ridan-to'g'ri havola qaytariladi.
    public function getVideoUrlAttribute(): ?string
    {
        if (in_array($this->source, ['youtube', 'vimeo'], true)) {
            return $this->url;
        }

        return $this->path_720 ? Storage::disk('public')->url($this->path_720) : null;
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
