<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Xodimga yuborilgan "kech qoldingiz" / "erta ketdingiz" Telegram
 * xabarining jurnali — NotifyStaffAttendance buyrug'i bir xil kun+tur
 * uchun ikkinchi marta xabar yubormasligi uchun shu jadval tekshiriladi.
 */
class AttendanceNotification extends Model
{
    public const TYPE_LATE = 'late';
    public const TYPE_EARLY = 'early';

    protected $fillable = ['user_id', 'date', 'type', 'minutes', 'sent_at'];

    protected function casts(): array
    {
        return [
            'date'    => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
