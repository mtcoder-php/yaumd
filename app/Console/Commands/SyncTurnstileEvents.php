<?php

namespace App\Console\Commands;

use App\Models\TurnstileDevice;
use App\Models\TurnstileEvent;
use App\Services\HikvisionTerminalClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Har bir faol turniket terminalidan yangi voqealarni (ISAPI AcsEvent
 * orqali) so'rab, 'turnstile_events' jadvaliga yozadi.
 *
 * MUHIM XAVFSIZLIK XUSUSIYATI: bu buyruq terminalning HECH QANDAY
 * sozlamasini o'zgartirmaydi — faqat o'qish (GET/qidiruv) so'rovlari
 * yuboradi. Shu sababli hozirgi ishlab turgan eski tizimga (HTTP Listening
 * orqali 192.168.30.28 ga yuborilayotgan xabarlarga) hech qanday ta'sir
 * qilmaydi — ikkalasi bir vaqtda, mustaqil ishlayveradi.
 *
 * Har bir terminal uchun "last_synced_at" ustuni orqali "qayerdan davom
 * etish" kursori saqlanadi, shuning uchun buyruq har necha daqiqada
 * ishlasa ham, hech qanday voqea o'tkazib yuborilmaydi va ikki marta ham
 * yozilmaydi (serial_no bo'yicha UNIQUE cheklov).
 */
class SyncTurnstileEvents extends Command
{
    protected $signature = 'turnstile:sync-events {--device= : Faqat shu ID yoki nomdagi terminalni sinxronlash}';

    protected $description = "Hikvision Face ID terminallardan yangi kirish-chiqish voqealarini so'rab oladi";

    public function handle(): int
    {
        $username = config('services.hikvision.username');
        $password = config('services.hikvision.password');

        if (! $username || ! $password) {
            $this->error("HIKVISION_USERNAME / HIKVISION_PASSWORD .env'da to'ldirilmagan.");

            return self::FAILURE;
        }

        $devices = TurnstileDevice::query()->where('is_active', true);

        if ($deviceFilter = $this->option('device')) {
            $devices->where(function ($q) use ($deviceFilter) {
                $q->where('id', $deviceFilter)->orWhere('name', $deviceFilter);
            });
        }

        $devices = $devices->get();

        if ($devices->isEmpty()) {
            $this->warn('Faol terminal topilmadi.');

            return self::SUCCESS;
        }

        $totalNew = 0;
        $failedDevices = 0;

        foreach ($devices as $device) {
            try {
                $newCount = $this->syncDevice($device, $username, $password);
                $totalNew += $newCount;
                $this->line("  {$device->name}: {$newCount} ta yangi voqea");
            } catch (Throwable $e) {
                $failedDevices++;
                $device->update(['last_error' => $e->getMessage()]);
                report($e);
                $this->error("  {$device->name}: XATO — {$e->getMessage()}");
            }
        }

        $this->info("Jami yangi voqealar: {$totalNew}. Muvaffaqiyatsiz terminallar: {$failedDevices}/{$devices->count()}.");

        return $failedDevices > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function syncDevice(TurnstileDevice $device, string $username, string $password): int
    {
        // Birinchi marta sinxronlanayotgan bo'lsa, butun tarixni tortib
        // olmaslik uchun faqat oxirgi 1 soatdan boshlaymiz (xohlasa, admin
        // buni keyinchalik alohida "backfill" buyrug'i bilan kengaytirishi
        // mumkin bo'ladi).
        $since = $device->last_synced_at ?? now()->subHour();
        $until = now();

        $client = new HikvisionTerminalClient($device, $username, $password);
        $result = $client->fetchAccessControlEvents($since, $until);

        $newCount = 0;

        foreach ($result['events'] as $row) {
            $serialNo = $row['serialNo'] ?? null;

            if ($serialNo === null || ! isset($row['time'])) {
                continue;
            }

            [, $created] = [null, false];

            $event = TurnstileEvent::firstOrNew([
                'turnstile_device_id' => $device->id,
                'serial_no' => $serialNo,
            ]);

            if ($event->exists) {
                continue;
            }

            $event->fill([
                // ->utc() SHART — sabab HikvisionTerminalClient'dagi izohda
                // tushuntirilgan (terminal "+05:00" bilan yuboradi, UTC'ga
                // aylantirmasdan saqlash noto'g'ri vaqt yozib qo'yadi).
                'event_time' => Carbon::parse($row['time'])->utc(),
                'major' => $row['major'] ?? 0,
                'minor' => $row['minor'] ?? 0,
                'employee_no' => $row['employeeNoString'] ?? null,
                'person_name' => $row['name'] ?? null,
                'door_no' => $row['doorNo'] ?? null,
                'card_reader_no' => $row['cardReaderNo'] ?? null,
                'verify_mode' => $row['currentVerifyMode'] ?? null,
                'picture_url' => $row['pictureURL'] ?? null,
                'raw_payload' => $row,
            ]);
            $event->save();

            $newCount++;
        }

        $device->update([
            'last_synced_at' => $result['lastEventTime'] ?? $until,
            'last_serial_no' => $result['events'] ? end($result['events'])['serialNo'] ?? $device->last_serial_no : $device->last_serial_no,
            'last_error' => null,
        ]);

        return $newCount;
    }
}
