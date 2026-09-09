<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'applicant_id', 'student_id', 'direction_id', 'contract_number',
        'amount', 'payment_type', 'status',
        'pdf_path', 'qr_code', 'otp_code',
        'otp_expires_at', 'signed_at',
        // Chegirma: 'base_amount' — chegirmasiz to'liq narx ("gross"),
        // 'amount' — chegirma qo'llangandan keyin haqiqiy to'lanadigan
        // summa ("net", boshqa joylarda o'zgarishsiz shu ma'noda ishlatiladi).
        'base_amount', 'discount_percent', 'discount_reason', 'discount_note',
    ];

    protected $hidden = ['otp_code'];

    protected $appends = ['person', 'discount_amount'];

    // CrmReportController va Contracts/Create-Edit.vue shu ro'yxatni
    // ishlatadi — sabab kaliti bitta joyda saqlanadi.
    public const DISCOUNT_REASONS = [
        'family'     => 'Oilaviy sharoit',
        'orphan'     => 'Yetim',
        'disability' => 'Nogironligi bor',
        'low_income' => "Kam ta'minlangan",
        'other'      => 'Boshqa',
    ];

    public const DISCOUNT_PERCENTS = [0, 10, 20, 25, 50, 75, 100];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'base_amount'      => 'decimal:2',
            'discount_percent' => 'integer',
            'otp_expires_at'   => 'datetime',
            'signed_at'        => 'datetime',
        ];
    }

    /**
     * Chegirma tufayli "yo'qotilgan" summa (gross - net). Chegirma
     * bo'lmasa yoki eski yozuvlarda base_amount hali to'ldirilmagan
     * bo'lsa — 0.
     */
    public function getDiscountAmountAttribute(): float
    {
        if (!$this->discount_percent || $this->base_amount === null) {
            return 0.0;
        }

        return round((float) $this->base_amount - (float) $this->amount, 2);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Kontrakt Abituriyentlar oqimi orqali (applicant_id to'ldirilgan) yoki
     * talaba to'g'ridan-to'g'ri kiritilganda (student_id to'ldirilgan)
     * yaratilishi mumkin. Shaxs ma'lumotlarini ikkala holatda ham bir xil
     * nom orqali olish uchun.
     */
    public function getPersonAttribute(): Applicant|Student|null
    {
        return $this->applicant ?? $this->student;
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'BK' . random_int(100000000, 999999999);
        } while (self::withTrashed()->where('contract_number', $number)->exists());

        return $number;
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // To'langan summani qayta hisoblab, shartnoma holatini shunga
    // moslashtiradi. Click/Payme orqali onlayn to'lov tasdiqlangan
    // (ContractClickCallbackController / ContractPaymeCallbackController)
    // yoki bekor qilingan/qaytarilgan har safar chaqiriladi — shu sababli
    // "to'langan" holati har doim haqiqiy to'lovlar yig'indisiga mos keladi
    // (masalan, to'lov keyinchalik bekor qilinsa, holat "imzolangan"ga
    // qaytariladi, "to'langan" bo'lib qolavermaydi).
    public function refreshStatusFromPayments(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $totalPaid = $this->payments()->where('status', 'paid')->sum('amount');

        if ($totalPaid >= $this->amount) {
            if ($this->status !== 'paid') {
                $this->update(['status' => 'paid']);
            }
        } elseif ($this->status === 'paid') {
            $this->update(['status' => 'signed']);
        }
    }
}
