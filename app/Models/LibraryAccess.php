<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryAccess extends Model
{
    // MUHIM: jadval nomi 'library_access' (BIRLIK) qilib yaratilgan
    // (dastlabki migratsiyada), Eloquent esa model nomidan avtomatik
    // 'library_accesses' (KO'PLIK) deb taxmin qiladi — shu farq sababli
    // "Base table or view not found: library_accesses" xatosi chiqadi.
    // Haqiqiy jadval nomini aniq ko'rsatib qo'yamiz.
    protected $table = 'library_access';

    protected $fillable = [
        'user_id', 'book_id', 'payment_order_id',
        'access_type', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function paymentOrder(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class, 'payment_order_id');
    }
}
