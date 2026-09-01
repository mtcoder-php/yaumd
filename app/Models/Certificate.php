<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'course_id', 'user_id', 'enrollment_id',
        'certificate_number', 'pdf_path', 'qr_code',
        'final_score', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at'   => 'datetime',
            'final_score' => 'decimal:2',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Yagona sertifikat raqamini generatsiya qiladi (masalan
     * "YAU-CERT-2026-004821"). Contract::generateNumber() bilan bir xil
     * naqshda — takrorlanmasligi bazadan tekshirilib turiladi.
     */
    public static function generateNumber(): string
    {
        do {
            $number = 'YAU-CERT-'.date('Y').'-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('certificate_number', $number)->exists());

        return $number;
    }
}
