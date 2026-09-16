<?php

namespace Database\Seeders;

use App\Models\TurnstileDevice;
use Illuminate\Database\Seeder;

/**
 * 12 ta haqiqiy Face ID terminalining ro'yxati (Mukhtor tomonidan
 * berilgan). Bu — asosiy DatabaseSeeder zanjiriga QASDDAN qo'shilmagan
 * (demo/dev ma'lumot emas, haqiqiy production infratuzilmasi), shuning
 * uchun kerak bo'lganda alohida ishga tushiriladi:
 *
 *   php artisan db:seed --class=Database\\Seeders\\TurnstileDeviceSeeder
 *
 * updateOrCreate ishlatilgani uchun buyruqni istalgancha marta qayta
 * ishga tushirish xavfsiz (mavjud yozuvlar ustiga qayta yozadi, dublikat
 * yaratmaydi).
 *
 * MUHIM: hozircha 'host' sifatida tashqi manzil (90.156.195.197) va port
 * ishlatilgan — bu curl orqali bevosita tekshirilib, ishlashi tasdiqlangan.
 * Agar kelajakda YAUMD serveri terminallar bilan bitta ichki tarmoqda
 * (192.168.91.x) joylashtirilsa, quyidagi 'host' qiymatlarini shu
 * terminallarning ichki IP'siga almashtirish kifoya — LEKIN ehtiyot
 * bo'ling: terminalning o'z HTTP porti (Configuration > Network >
 * Network Service > HTTP(S)) ko'rilgan namunada 80 edi, tashqi port esa
 * NAT orqali beriladigan boshqa raqam — ya'ni ichki tarmoqqa o'tganda
 * port ham 80'ga o'zgarishi kerak bo'lishi mumkin, buni albatta oldindan
 * tekshirib ko'ring.
 */
class TurnstileDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $externalHost = '90.156.195.197';

        $devices = [
            ['name' => 'chiqish-5', 'port' => 82],
            ['name' => 'kirganda-ong-taraf-2', 'port' => 83],
            ['name' => 'kirganda-ong-taraf-3', 'port' => 84],
            ['name' => 'kirganda-ong-taraf-1', 'port' => 85],
            ['name' => 'chiqish-1', 'port' => 86],
            ['name' => 'chiqish-2', 'port' => 94],
            ['name' => 'chiqish-3', 'port' => 88],
            ['name' => 'chiqish-4', 'port' => 89],
            ['name' => 'chiqish-6', 'port' => 90],
            ['name' => 'kirganda-chap-taraf-1', 'port' => 91],
            ['name' => 'kirganda-chap-taraf-3', 'port' => 92],
            ['name' => 'kirganda-chap-taraf-2', 'port' => 93],
        ];

        foreach ($devices as $device) {
            TurnstileDevice::updateOrCreate(
                ['host' => $externalHost, 'port' => $device['port']],
                [
                    'name' => $device['name'],
                    'scheme' => 'http',
                    'direction' => str_contains($device['name'], 'kirganda') ? 'kirish' : 'chiqish',
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('12 ta turniket terminali qo\'shildi/yangilandi.');
    }
}
