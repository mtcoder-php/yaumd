<?php

namespace App\Models;

use App\Contracts\Purchasable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Course extends Model implements Purchasable
{
    use SoftDeletes;

    protected $appends = ['thumbnail_url'];

    protected $fillable = [
        'category_id', 'created_by',
        'title_uz', 'title_ru', 'title_en',
        'description_uz', 'description_ru', 'description_en',
        'what_you_learn', 'requirements',
        'thumbnail', 'promo_video',
        'type', 'scorm_type', 'level', 'language', 'degree',
        'price', 'discount_price', 'duration_hours',
        'has_certificate', 'is_sequential',
        'rating_avg', 'rating_count', 'students_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'what_you_learn'  => 'array',
            'requirements'    => 'array',
            'has_certificate' => 'boolean',
            'is_sequential'   => 'boolean',
            'price'           => 'decimal:2',
            'discount_price'  => 'decimal:2',
            'rating_avg'      => 'decimal:2',
        ];
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructors')->withPivot('role')->withTimestamps();
    }

    public function directions(): BelongsToMany
    {
        return $this->belongsToMany(Direction::class, 'course_directions')->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(StudentGroup::class, 'course_groups')->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(CourseRating::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // Bu kurs uchun ochilgan barcha to'lov buyurtmalari (Click/Payme) —
    // umumiy 'payment_orders' jadvaliga polimorfik bog'lanish orqali
    // (App\Models\PaymentOrder::payable()'ga qarang).
    public function payments(): MorphMany
    {
        return $this->morphMany(PaymentOrder::class, 'payable');
    }

    // --- App\Contracts\Purchasable ---

    public function purchasePrice(): float
    {
        return (float) ($this->discount_price > 0 ? $this->discount_price : $this->price);
    }

    public function canBePurchased(): bool
    {
        return $this->status === 'published' && $this->type === 'paid' && $this->purchasePrice() > 0;
    }

    // Talaba bu kursga ALLAQACHON (to'langan holda) yozilganmi. Diqqat:
    // StudentCourseController::findEnrollmentOrFail() faqat Enrollment
    // yozuvi mavjudligini tekshiradi (payment_status'ga qaramaydi) — shu
    // sababli yozuv "paid" bo'lishi shu yerda alohida talab qilinadi,
    // aks holda "pending" (hali to'lanmagan) buyurtma ham kursni ochib
    // qo'yishi mumkin edi.
    public function hasAccessFor(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->enrollments()
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->exists();
    }

    public function grantAccessFor(int $userId, PaymentOrder $order): void
    {
        Enrollment::updateOrCreate([
            'course_id' => $this->id,
            'user_id'   => $userId,
        ], [
            'payment_type'   => $order->provider,
            'payment_status' => 'paid',
            'amount'         => $order->amount,
            'transaction_id' => $order->transaction_id,
            'status'         => 'active',
            'enrolled_at'    => now(),
        ]);
    }

    // Yozuvni O'CHIRMAYMIZ — talaba allaqachon progress qildirgan bo'lishi
    // mumkin (masalan to'lov keyinchalik Payme tomonidan bekor qilinsa),
    // shuning uchun faqat to'lov holatini "failed" qilib qo'yamiz.
    public function revokeAccessFor(int $userId): void
    {
        $this->enrollments()->where('user_id', $userId)->update(['payment_status' => 'failed']);
    }
}
