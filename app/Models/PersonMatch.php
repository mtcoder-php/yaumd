<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Turniket terminalidagi "employeeNoString" bilan YAUMD'dagi haqiqiy
 * talaba/xodim yozuvi o'rtasidagi moslik — MatchTurnstilePeople buyrug'i
 * (avtomatik) va PersonMatchController (admin qo'lda) tomonidan
 * to'ldiriladi/yangilanadi. To'liq tushuntirish uchun migratsiyaga qarang.
 */
class PersonMatch extends Model
{
    public const STATUS_AUTO_MATCHED = 'auto_matched';
    public const STATUS_NEEDS_REVIEW = 'needs_review';
    public const STATUS_MATCHED = 'matched';
    public const STATUS_UNMATCHED = 'unmatched';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'employee_no',
        'person_name',
        'matchable_type',
        'matchable_id',
        'status',
        'confidence',
        'candidates',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'candidates' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function matchable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Bu employee_no allaqachon (avtomatik yoki qo'lda) YAUMD yozuviga
     * bog'langanmi — ya'ni turnstile_events.matched_type/matched_id
     * shu asosda to'ldirilishi kerakmi?
     */
    public function isResolved(): bool
    {
        return in_array($this->status, [self::STATUS_AUTO_MATCHED, self::STATUS_MATCHED], true)
            && $this->matchable_type !== null
            && $this->matchable_id !== null;
    }

    /**
     * Shu employee_no'ga tegishli barcha turniket voqealarining
     * 'matched_type'/'matched_id' ustunlarini joriy holatga moslashtiradi
     * (tasdiqlanganda to'ldiriladi, rad etilganda/bekor qilinganda
     * tozalanadi) — Phase 3 (yo'qlama) hisobotlari to'g'ridan-to'g'ri
     * shu ustunlar orqali tez so'rov qila olishi uchun.
     */
    public function syncEventsMatch(): void
    {
        TurnstileEvent::query()
            ->where('employee_no', $this->employee_no)
            ->update([
                'matched_type' => $this->isResolved() ? $this->matchable_type : null,
                'matched_id' => $this->isResolved() ? $this->matchable_id : null,
            ]);
    }
}
