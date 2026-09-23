<?php

namespace App\Console\Commands;

use App\Models\AttendanceNotification;
use App\Models\PersonMatch;
use App\Models\Setting;
use App\Models\TurnstileEvent;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Har kuni ish kuni yakunida (kechqurun) ishga tushiriladigan buyruq —
 * Telegram botga ulangan har bir xodim (PersonMatch orqali turniketga
 * moslashtirilgan 'staff' yozuvi) uchun bugungi BIRINCHI "kirish" va
 * OXIRGI "chiqish" voqealarini standart ish vaqti bilan solishtiradi:
 *
 *   - birinchi kirish standart boshlanish vaqtidan KECHROQ bo'lsa —
 *     "N daqiqa kech qoldingiz";
 *   - oxirgi chiqish standart tugash vaqtidan OLDINROQ bo'lsa —
 *     "N daqiqa erta ketdingiz".
 *
 * "OXIRGI chiqish" faqat kun TUGAGANDA (kechqurun) aniq bilinadi — shuning
 * uchun bu buyruq kun davomida emas, FAQAT kechqurun bir marta ishga
 * tushiriladi (aks holda tushlik uchun chiqib-kirish "erta ketish" deb
 * noto'g'ri hisoblanib qolardi).
 *
 * Standart ish vaqti Setting orqali sozlanadi (admin panelda o'zgartirish
 * mumkin bo'lishi uchun) — sozlanmagan bo'lsa Mukhtor bergan standart
 * 08:00–17:00 ishlatiladi.
 */
class NotifyStaffAttendance extends Command
{
    protected $signature = 'attendance:notify-staff {--date= : Tekshiriladigan sana (Y-m-d), standart — bugun}';

    protected $description = "Xodimlarning bugungi birinchi kirish/oxirgi chiqish vaqtini ish vaqti bilan solishtirib, kech qolish/erta ketish haqida Telegram xabar yuboradi";

    public function handle(TelegramService $telegram): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();

        $workStart = (string) Setting::get('attendance.work_start', '08:00');
        $workEnd = (string) Setting::get('attendance.work_end', '17:00');

        $expectedStart = $this->combineDateAndTime($date, $workStart);
        $expectedEnd = $this->combineDateAndTime($date, $workEnd);

        $matches = PersonMatch::query()
            ->whereIn('status', [PersonMatch::STATUS_AUTO_MATCHED, PersonMatch::STATUS_MATCHED])
            ->where('matchable_type', 'staff')
            ->with('matchable')
            ->get();

        if ($matches->isEmpty()) {
            $this->warn("Hali hech qanday xodim moslashtirilmagan — avval 'turnstile:match-people' ishga tushiring.");

            return self::SUCCESS;
        }

        $lateCount = 0;
        $earlyCount = 0;

        foreach ($matches as $match) {
            $user = $match->matchable;

            if (! $user instanceof User || ! $user->telegram_chat_id) {
                continue;
            }

            $events = TurnstileEvent::query()
                ->where('matched_type', 'staff')
                ->where('matched_id', $user->id)
                ->whereBetween('event_time', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->where('major', TurnstileEvent::MAJOR_ACCESS_CONTROL)
                ->where('minor', TurnstileEvent::MINOR_FACE_RECOGNIZED)
                ->with('device')
                ->orderBy('event_time')
                ->get();

            // MUHIM: Collection::firstWhere() yopiq funksiya (closure) emas,
            // maydon nomini kutadi — shu sababli bu yerda ATAYLAB oddiy
            // first()/last() ishlatiladi, ularning ikkalasi ham predikat
            // sifatida closure qabul qiladi.
            $firstEntry = $events->first(fn (TurnstileEvent $e) => $e->device?->direction === 'kirish');
            $lastExit = $events->last(fn (TurnstileEvent $e) => $e->device?->direction === 'chiqish');

            if ($firstEntry && $firstEntry->event_time->greaterThan($expectedStart)) {
                $minutes = (int) round($expectedStart->diffInMinutes($firstEntry->event_time));

                if ($minutes > 0 && $this->notifyOnce($user, $date, AttendanceNotification::TYPE_LATE, $minutes, $telegram,
                        "⏰ Bugun soat <b>{$firstEntry->event_time->format('H:i')}</b> da keldingiz — belgilangan {$workStart} dan <b>{$minutes} daqiqa</b> kech qoldingiz."
                    )) {
                    $lateCount++;
                }
            }

            if ($lastExit && $lastExit->event_time->lessThan($expectedEnd)) {
                $minutes = (int) round($lastExit->event_time->diffInMinutes($expectedEnd));

                if ($minutes > 0 && $this->notifyOnce($user, $date, AttendanceNotification::TYPE_EARLY, $minutes, $telegram,
                        "🚪 Bugun soat <b>{$lastExit->event_time->format('H:i')}</b> da chiqdingiz — belgilangan {$workEnd} dan <b>{$minutes} daqiqa</b> erta ketdingiz."
                    )) {
                    $earlyCount++;
                }
            }
        }

        $this->info("Kech qolish xabari: {$lateCount}. Erta ketish xabari: {$earlyCount}.");

        return self::SUCCESS;
    }

    private function combineDateAndTime(Carbon $date, string $time): Carbon
    {
        [$hour, $minute] = array_pad(explode(':', $time), 2, '0');

        return $date->copy()->setTime((int) $hour, (int) $minute, 0);
    }

    private function notifyOnce(User $user, Carbon $date, string $type, int $minutes, TelegramService $telegram, string $message): bool
    {
        $exists = AttendanceNotification::where('user_id', $user->id)
            ->whereDate('date', $date->toDateString())
            ->where('type', $type)
            ->exists();

        if ($exists) {
            return false;
        }

        $telegram->sendMessage($user->telegram_chat_id, $message);

        AttendanceNotification::create([
            'user_id' => $user->id,
            'date'    => $date->toDateString(),
            'type'    => $type,
            'minutes' => $minutes,
            'sent_at' => now(),
        ]);

        return true;
    }
}
