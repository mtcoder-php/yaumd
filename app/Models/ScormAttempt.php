<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormAttempt extends Model
{
    protected $fillable = [
        'scorm_package_id', 'lesson_id', 'user_id',
        'attempt_id', 'completion_status', 'success_status',
        'score_raw', 'score_min', 'score_max', 'score_scaled',
        'session_time', 'total_time',
        'suspend_data', 'interactions', 'objectives',
    ];

    protected function casts(): array
    {
        return [
            'suspend_data' => 'array',
            'interactions' => 'array',
            'objectives'   => 'array',
        ];
    }

    public function scormPackage(): BelongsTo
    {
        return $this->belongsTo(ScormPackage::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
