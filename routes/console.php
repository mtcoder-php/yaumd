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
//
// MUHIM: quyidagi uchtasi ATAYLAB shu tartibda ro'yxatdan o'tkazilgan
// (Laravel scheduler bir xil daqiqada tayinlangan buyruqlarni FAQAT
// ->runInBackground() ishlatilmasa, ro'yxatga olingan tartibda ketma-ket
// bajaradi): avval yangi voqealar yoziladi, keyin ularning employee_no'si
// talaba/xodim yozuviga moslashtiriladi, ENG OXIRIDA esa yangi
// moslashtirilgan talaba voqealari uchun Telegram xabari yuboriladi —
// aks holda notify-students hali match-people ko'rmagan voqealarni
// tashlab ketardi.
Schedule::command('turnstile:sync-events')->everyMinute()->withoutOverlapping();

// Yangi employee_no'larni (va oldin hal qilinmagan qolganlarini) talaba/
// xodim yozuviga ism bo'yicha moslashtiradi (MatchTurnstilePeople'ga
// qarang). Idempotent — admin qo'lda tasdiqlagan/rad etganiga tegmaydi.
Schedule::command('turnstile:match-people')->everyFiveMinutes()->withoutOverlapping();

// Talabaga turniketdan o'tgani haqida (kirdi/chiqdi vaqti + to'lov holati)
// darhol Telegram xabari yuboradi (NotifyStudentTurnstileEvents'ga qarang).
Schedule::command('turnstile:notify-students')->everyMinute()->withoutOverlapping();

// Har kuni ish kuni tugagach, xodimlarning bugungi birinchi kirish/oxirgi
// chiqish vaqtini standart ish vaqti (08:00–17:00, Setting orqali
// sozlanadi) bilan solishtirib, kech qolish/erta ketish haqida Telegram
// xabari yuboradi (NotifyStaffAttendance'ga qarang). 20:00'da ishga
// tushiriladi — bu vaqtga kelib deyarli barcha xodimning kunlik oxirgi
// chiqish voqeasi allaqachon ro'y bergan bo'ladi.
Schedule::command('attendance:notify-staff')->dailyAt('20:00');
