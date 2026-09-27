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
}
