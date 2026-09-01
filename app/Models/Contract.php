<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'applicant_id', 'student_id', 'direction_id', 'contract_number',
        'amount', 'payment_type', 'status',
        'pdf_path', 'qr_code', 'otp_code',
        'otp_expires_at', 'signed_at',
    ];

    protected $hidden = ['otp_code'];

    protected $appends = ['person'];

    protected function casts(): array
    {
        return [
            'amount'          => 'decimal:2',
            'otp_expires_at'  => 'datetime',
            'signed_at'       => 'datetime',
        ];
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
}
