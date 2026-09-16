<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Terminaldan ISAPI AcsEvent orqali olingan bitta xom voqea.
 *
 * MUHIM: hozircha faqat major=5 (AccessControl) voqealari yig'iladi
 * (SyncTurnstileEvents'ga qarang). Amalda ko'rilgan minor kodlar:
 *   - 75  => yuz muvaffaqiyatli tanildi, eshik ochildi (kirish/chiqish
 *            voqeasi — employeeNoString va 'name' shu yozuvda keladi)
 *   - 21  => eshik qulfdan ochildi (texnik, odamga bog'liq emas)
 *   - 22  => eshik qulflandi (texnik, odamga bog'liq emas)
 * Boshqa minor kodlar (masalan rad etilgan urinish) hali kuzatilmagan —
 * shuning uchun bularni ham xatosiz saqlash uchun 'raw_payload' to'liq
 * saqlanadi va kodlar keyinchalik shu yerga qo'shiladi.
 */
class TurnstileEvent extends Model
{
    public const MAJOR_ACCESS_CONTROL = 5;
    public const MINOR_FACE_RECOGNIZED = 75;
    public const MINOR_DOOR_UNLOCKED = 21;
    public const MINOR_DOOR_LOCKED = 22;

    protected $fillable = [
        'turnstile_device_id',
        'serial_no',
        'event_time',
        'major',
        'minor',
        'employee_no',
        'person_name',
        'door_no',
        'card_reader_no',
        'verify_mode',
        'picture_url',
        'raw_payload',
        'matched_type',
        'matched_id',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(TurnstileDevice::class, 'turnstile_device_id');
    }

    /**
     * Bu — real (odam bilan bog'liq) "kirish/chiqish" voqeasimi, yoki
     * shunchaki eshikning texnik ochilish/yopilish signalimi?
     */
    public function isIdentificationEvent(): bool
    {
        return $this->major === self::MAJOR_ACCESS_CONTROL
            && $this->minor === self::MINOR_FACE_RECOGNIZED;
    }
}
