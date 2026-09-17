<?php

namespace App\Console\Commands;

use App\Models\TurnstileDevice;
use App\Services\HikvisionTerminalClient;
use Illuminate\Console\Command;

/**
 * TO'LIQ XAVFSIZ — FAQAT O'QISH. Terminaldagi bitta shaxsning joriy
 * Valid.enable holatini ko'rsatadi, hech narsani o'zgartirmaydi.
 *
 * Ishlatilishi: TestTurnstileAccessToggle orqali qarzdor bir talabani
 * --disable qilgach, buni HECH NARSANI o'zgartirmasdan, vaqti-vaqti bilan
 * (masalan bir necha soatdan keyin, ertasi kuni) qayta ishga tushirib,
 * holat hali ham o'zgarganicha turibdimi yoki eski tizim uni qaytarib
 * qo'yganmi (ya'ni yana 'true'ga o'zgarib qolganmi) — shuni bilish uchun.
 */
class CheckTurnstileAccessStatus extends Command
{
    protected $signature = 'turnstile:check-access-status
        {employeeNo : Tekshiriladigan shaxsning employeeNo\'si}
        {--device= : Terminal ID yoki nomi (MAJBURIY)}';

    protected $description = "(FAQAT O'QISH) Bitta shaxsning joriy Valid.enable holatini bitta terminalda ko'rsatadi";

    public function handle(): int
    {
        $employeeNo = (string) $this->argument('employeeNo');
        $deviceOption = $this->option('device');

        if (! $deviceOption) {
            $this->error('--device MAJBURIY — masalan --device=chiqish-5.');

            return self::FAILURE;
        }

        $device = TurnstileDevice::query()
            ->where('id', $deviceOption)
            ->orWhere('name', $deviceOption)
            ->first();

        if (! $device) {
            $this->error("Terminal topilmadi: {$deviceOption}");

            return self::FAILURE;
        }

        $username = config('services.hikvision.username');
        $password = config('services.hikvision.password');

        if (! $username || ! $password) {
            $this->error("HIKVISION_USERNAME / HIKVISION_PASSWORD .env'da to'ldirilmagan.");

            return self::FAILURE;
        }

        $client = new HikvisionTerminalClient($device, $username, $password);
        $info = $client->searchUserInfo($employeeNo);

        if (! $info) {
            $this->error("\"{$employeeNo}\" {$device->name} terminalida topilmadi.");

            return self::FAILURE;
        }

        $this->line('Vaqt: ' . now()->format('Y-m-d H:i:s') . ' (server, ' . config('app.timezone') . ')');
        $this->line("Terminal: {$device->name}");
        $this->line("Ism: {$info['name']}");
        $this->line('Valid.enable: ' . ($info['Valid']['enable'] ? 'true (YOQILGAN)' : 'false (O\'CHIRILGAN)'));
        $this->line("Valid davri: {$info['Valid']['beginTime']} — {$info['Valid']['endTime']}");
        $this->line('Yuz soni: ' . ($info['numOfFace'] ?? '—'));

        return self::SUCCESS;
    }
}
