<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * xAPI (Tin Can) kontenti yuborgan har bir "statement"ning yozuvi.
 * "Amaliy" LRS — rasmiy Learning Record Store spetsifikatsiyasining
 * to'liq konformansi emas, lekin real xAPI paketlaridan (Articulate
 * Rise/Storyline va h.k.) kelayotgan statement'larni qabul qilib,
 * ular asosida tugallanish/o'tish holatini kuzatish uchun yetarli.
 */
class XapiStatement extends Model
{
    protected $fillable = [
        'scorm_package_id', 'lesson_id', 'user_id',
        'statement_id', 'verb_id', 'verb_display', 'object_id',
        'result_completion', 'result_success',
        'result_score_scaled', 'result_score_raw', 'result_duration',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'result_completion' => 'boolean',
            'result_success'    => 'boolean',
            'raw'               => 'array',
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

    public function scormPackage(): BelongsTo
    {
        return $this->belongsTo(ScormPackage::class);
    }
}
