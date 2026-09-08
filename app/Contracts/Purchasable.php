<?php

namespace App\Contracts;

use App\Models\PaymentOrder;

/**
 * Pullik narsani (elektron kitob, kurs va h.k.) Click/Payme orqali sotib
 * olish mumkin bo'lishi uchun shu interfeysni amalga oshirish kifoya —
 * PaymentCheckoutService va ClickCallbackController/PaymeCallbackController
 * FAQAT shu metodlar orqali ishlaydi, "kitobmi yoki kursmi"ni bilishlari
 * shart emas. Yangi turdagi pullik narsa (masalan, seminar) qo'shish uchun
 * ham xuddi shu interfeysni amalga oshirish yetarli bo'ladi.
 */
interface Purchasable
{
    // Narx (so'mda). canBePurchased() true bo'lganda shu narxga to'lov
    // buyurtmasi (PaymentOrder) ochiladi.
    public function purchasePrice(): float;

    // Bu obyektni umuman pul evaziga sotib olish mumkinmi (masalan, kitob
    // uchun access_type === 'paid' va narx > 0, kurs uchun status ===
    // 'published' va type === 'paid').
    public function canBePurchased(): bool;

    // Berilgan foydalanuvchi bu obyektga (kitob fayliga, kurs darslariga)
    // ALLAQACHON ruxsati bormi.
    public function hasAccessFor(?int $userId): bool;

    // To'lov muvaffaqiyatli tasdiqlanganda (Click "Complete" yoki Payme
    // "PerformTransaction") chaqiriladi — ruxsatni beradigan yozuvni
    // (LibraryAccess, Enrollment va h.k.) yaratadi/yangilaydi.
    public function grantAccessFor(int $userId, PaymentOrder $order): void;

    // To'lov bekor qilinganda (Payme "CancelTransaction", to'lov
    // performTransaction'dan KEYIN bekor qilinsa) chaqiriladi.
    public function revokeAccessFor(int $userId): void;
}
