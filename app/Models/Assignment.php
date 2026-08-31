<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'lesson_id', 'title_uz', 'title_ru',
        'description_uz', 'description_ru',
        'max_score', 'passing_score', 'deadline',
        'allow_late', 'allow_resubmit', 'max_attempts',
        'submission_type', 'allowed_file_types', 'max_file_size',
    ];

    protected function casts(): array
    {
        return [
            'deadline'           => 'datetime',
            'allow_late'         => 'boolean',
            'allow_resubmit'     => 'boolean',
            'allowed_file_types' => 'array',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
