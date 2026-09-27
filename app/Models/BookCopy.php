<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookCopy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'book_id', 'inventory_code', 'status', 'condition_notes',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    /**
     * Hozir kimningdir qo'lida bo'lsa, aynan shu "berish" yozuvi — Show.vue
     * kimga va qachongacha berilganini shu orqali ko'rsatadi. `latestOfMany`
     * emas, `status` bo'yicha filtrlangan `HasOne` ishlatiladi, chunki bitta
     * nusxaning tarixida bir nechta 'returned' yozuv bo'lishi mumkin — bizga
     * FAQAT hali yopilmagani kerak.
     */
    public function activeLoan(): HasOne
    {
        return $this->hasOne(BookLoan::class)->where('status', BookLoan::STATUS_ACTIVE)->latestOfMany();
    }

    /**
     * Yangi nusxalar uchun ketma-ket inventar raqamlari — "KUT-000001"
     * shaklida (Show.vue'dagi eski qo'lda kiritish namunasi bilan bir xil
     * format). 'inventory_code' butun jadval bo'yicha GLOBAL unikal ustun
     * (book_id'ga bog'liq emas) bo'lgani uchun, mavjud eng katta raqamdan
     * davom etiladi — o'chirilgan (soft-delete) yozuvlar ham unique
     * cheklovni band qilib turgani uchun withTrashed() bilan hisobga
     * olinadi.
     *
     * @return list<string>
     */
    public static function nextInventoryCodes(int $count): array
    {
        $prefix = 'KUT-';

        $maxNumber = static::withTrashed()
            ->where('inventory_code', 'like', "{$prefix}%")
            ->pluck('inventory_code')
            ->map(fn (string $code) => (int) preg_replace('/\D/', '', substr($code, strlen($prefix))))
            ->max() ?? 0;

        $codes = [];

        for ($i = 1; $i <= $count; $i++) {
            $codes[] = $prefix . str_pad((string) ($maxNumber + $i), 6, '0', STR_PAD_LEFT);
        }

        return $codes;
    }
}
