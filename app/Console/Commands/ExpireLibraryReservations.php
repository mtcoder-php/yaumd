<?php

namespace App\Console\Commands;

use App\Services\LibraryReservationService;
use Illuminate\Console\Command;

/**
 * "Tayyor" (ready) holatidagi band qilishlardan muddati (odatda 24 soat)
 * o'tib ketib, hali kutubxonaga kelib olinmaganlarini topib, 'expired'
 * qilib yopadi va nusxani avtomatik navbatdagi keyingi shaxsga o'tkazadi
 * (LibraryReservationService::expireStale()ga qarang). Har 30 daqiqada
 * ishga tushiriladi — kunlik emas, chunki 24 soatlik oyna kuniga bir marta
 * tekshirilsa, ko'p soat kechikish bilan navbat surilib qolishi mumkin edi.
 */
class ExpireLibraryReservations extends Command
{
    protected $signature = 'library:expire-reservations';

    protected $description = "Muddati o'tgan kitob band qilishlarni (rezervatsiya) bekor qilib, navbatni keyingi shaxsga suradi";

    public function handle(LibraryReservationService $reservations): int
    {
        $count = $reservations->expireStale();

        $this->info("Muddati o'tkazilgan band qilishlar: {$count}");

        return self::SUCCESS;
    }
}
