<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Har kuni ertalab talabalarga to'lov muddati eslatmalarini yuboradi
// (SendPaymentReminders'ga qarang). MUHIM: bu jadval faqat Laravel'ning
// o'z ichidagi ro'yxat — HAQIQIY ishga tushishi uchun serverning
// crontab'iga albatta shu qator qo'shilishi shart:
//   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('payments:send-reminders')->dailyAt('09:00');

// Har daqiqada barcha faol turniket terminallaridan yangi kirish-chiqish
// voqealarini so'rab, 'turnstile_events' jadvaliga yozadi (SyncTurnstileEvents'ga
// qarang). Bu FAQAT o'qish (GET/qidiruv) so'rovlari yuboradi — terminalning
// hech qanday sozlamasini o'zgartirmaydi, shuning uchun hozirgi ishlab turgan
// eski tizimga (HTTP Listening) hech qanday ta'sir qilmaydi.
Schedule::command('turnstile:sync-events')->everyMinute()->withoutOverlapping();
