<?php

namespace App\Models;

use App\Contracts\Purchasable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class LibraryBook extends Model implements Purchasable
{
    use SoftDeletes;

    protected $appends = ['cover_image_url', 'has_digital_file'];

    protected $fillable = [
        'category_id', 'isbn', 'title', 'author',
        'publisher', 'published_year', 'language',
        'description', 'cover_image', 'file_path',
        'page_count', 'shelf_location', 'added_by',
        'access_type', 'price',
        'download_count', 'view_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    // Fayl o'zi hech qachon frontendga chiqarilmaydi (private disk'da
    // saqlanadi, faqat ruxsati bor foydalanuvchi controller orqali yuklab
    // oladi) — shuning uchun Vue sahifalariga faqat "fayl mavjudmi" degan
    // boolean beriladi, haqiqiy yo'l emas.
    public function getHasDigitalFileAttribute(): bool
    {
        return (bool) $this->file_path;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(LibraryAccess::class, 'book_id');
    }

    // Bu kitob uchun ochilgan barcha to'lov buyurtmalari (Click/Payme) —
    // umumiy 'payment_orders' jadvaliga polimorfik bog'lanish orqali
    // (App\Models\PaymentOrder::payable()'ga qarang).
    public function payments(): MorphMany
    {
        return $this->morphMany(PaymentOrder::class, 'payable');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'book_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // --- App\Contracts\Purchasable ---

    public function purchasePrice(): float
    {
        return (float) $this->price;
    }

    public function canBePurchased(): bool
    {
        return $this->access_type === 'paid' && $this->purchasePrice() > 0;
    }

    // Berilgan foydalanuvchi shu kitobning RAQAMLI faylini yuklab olishga
    // haqlimi: kitob bepul bo'lsa — ha; pullik bo'lsa — faqat muddati
    // o'tmagan (yoki muddatsiz) ruxsat yozuvi (LibraryAccess) mavjud bo'lsa.
    public function hasAccessFor(?int $userId): bool
    {
        if ($this->access_type === 'free') {
            return true;
        }

        if (! $userId) {
            return false;
        }

        return $this->accesses()
            ->where('user_id', $userId)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now()))
            ->exists();
    }

    public function grantAccessFor(int $userId, PaymentOrder $order): void
    {
        LibraryAccess::firstOrCreate([
            'user_id' => $userId,
            'book_id' => $this->id,
        ], [
            'payment_order_id' => $order->id,
            'access_type'      => 'purchased',
        ]);
    }

    public function revokeAccessFor(int $userId): void
    {
        $this->accesses()->where('user_id', $userId)->delete();
    }
}
