<?php

namespace App\Console\Commands;

use App\Models\TurnstileDevice;
use App\Services\HikvisionTerminalClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * MUHIM: bu Phase 4 (to'lov holatiga qarab turniketni avtomatik
 * ochish/yopish) uchun HALI HECH QANDAY AVTOMATIK narsa emas — bu FAQAT
 * qo'lda, ONG BIR MARTA ishga tushiriladigan SINOV vositasi.
 *
 * Maqsad: eski tizim terminaldagi "Valid.enable" maydonini qanday
 * boshqarayotgani noma'lum (avtomatik davriy push yoki kamdan-kam qo'lda).
 * Shu sababli YAUMD ham shu maydonni yozishni boshlashdan oldin, BITTA
 * aniq odam + BITTA aniq terminalda amalda nima bo'lishini ko'rish kerak:
 *   - Yozish umuman ishlaydimi (terminal so'rovni qabul qiladimi)?
 *   - Yozgach boshqa hech narsa (ism, yuz ma'lumoti) buzilmaydimi?
 *   - Bir necha daqiqa/soatdan keyin eski tizim buni "qaytarib qo'yadimi"
 *     (bu — ikkala tizim bir-birining ustidan yozayotganidan dalolat
 *     beradi va Phase 4'ni qanday loyihalash kerakligini belgilaydi)?
 *
 * XAVFSIZLIK TAVSIYASI: birinchi sinovni albatta HOZIRDA QARZI BOR
 * (demak eski tizim bo'yicha ham kirish huquqi bo'lmasligi kerak bo'lgan)
 * talaba ustida qiling — shunda hatto kutilmagan natija chiqsa ham,
 * haqiqiy ta'sir minimal bo'ladi. Test tugagach, --enable bilan holatni
 * albatta ORIGINAL holatiga qaytaring (agar u avval yoqilgan bo'lsa).
 */
class TestTurnstileAccessToggle extends Command
{
    protected $signature = 'turnstile:test-access-toggle
        {employeeNo : Sinov qilinadigan shaxsning employeeNo\'si (masalan s6236)}
        {--device= : Terminal ID yoki nomi (MAJBURIY — faqat BITTA terminalda sinaladi)}
        {--enable : Kirish huquqini YOQISH}
        {--disable : Kirish huquqini O\'CHIRISH}
        {--force : Tasdiqlash so\'ralmasdan darhol bajarish}';

    protected $description = "(SINOV UCHUN) Bitta shaxsning Valid.enable holatini bitta terminalda qo'lda o'zgartiradi va natijani ko'rsatadi";

    public function handle(): int
    {
        $employeeNo = (string) $this->argument('employeeNo');
        $enableFlag = (bool) $this->option('enable');
        $disableFlag = (bool) $this->option('disable');
        $deviceOption = $this->option('device');

        if ($enableFlag === $disableFlag) {
            $this->error('Aynan bittasini tanlang: --enable YOKI --disable (ikkalasi ham emas, hech biri ham emas).');

            return self::FAILURE;
        }

        if (! $deviceOption) {
            $this->error('--device MAJBURIY — masalan --device=chiqish-5 (faqat bitta terminalda sinash uchun ataylab shunday).');

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

        $this->info("Terminal: {$device->name} ({$device->baseUrl()})");
        $this->info("Joriy holatni o'qiyapman: {$employeeNo}...");

        $before = $client->searchUserInfo($employeeNo);

        if (! $before) {
            $this->error("\"{$employeeNo}\" bu terminalda topilmadi.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('=== HOZIRGI HOLAT ===');
        $this->line("  Ism: {$before['name']}");
        $this->line('  Valid.enable: ' . ($before['Valid']['enable'] ? 'true (yoqilgan)' : 'false (o\'chirilgan)'));
        $this->line("  Valid davri: {$before['Valid']['beginTime']} — {$before['Valid']['endTime']}");
        $this->line('  Yuz soni (numOfFace): ' . ($before['numOfFace'] ?? '—'));
        $this->line('  doorRight: ' . ($before['doorRight'] ?? '—'));
        $this->newLine();

        $targetLabel = $enableFlag ? 'YOQISH (true)' : "O'CHIRISH (false)";
        $this->warn("Bu buyruq \"{$employeeNo}\" uchun Valid.enable'ni {$targetLabel}ga o'zgartiradi — terminal: {$device->name}.");

        if (! $this->option('force') && ! $this->confirm('Davom etasizmi?', false)) {
            $this->line('Bekor qilindi.');

            return self::SUCCESS;
        }

        Log::warning('Turnstile Valid.enable sinov o\'zgarishi', [
            'employee_no' => $employeeNo,
            'device' => $device->name,
            'before' => $before['Valid'],
            'target_enable' => $enableFlag,
            'triggered_by' => 'turnstile:test-access-toggle (qo\'lda)',
        ]);

        $result = $client->setUserValid($employeeNo, $before['Valid'], $enableFlag);

        $this->newLine();
        $this->line('=== TERMINAL JAVOBI (Modify) ===');
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info('Qayta tekshiryapman (o\'qish)...');
        $after = $client->searchUserInfo($employeeNo);

        if (! $after) {
            $this->error('Qayta o\'qishda topilmadi — buni albatta terminalning o\'z veb-panelidan qo\'lda tekshiring!');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('=== YANGI HOLAT ===');
        $this->line("  Ism: {$after['name']} " . ($after['name'] === $before['name'] ? '(o\'zgarmadi ✓)' : '(!!! O\'ZGARDI !!!)'));
        $this->line('  Valid.enable: ' . ($after['Valid']['enable'] ? 'true (yoqilgan)' : 'false (o\'chirilgan)'));
        $this->line('  Yuz soni (numOfFace): ' . ($after['numOfFace'] ?? '—') . ' ' . (($after['numOfFace'] ?? null) === ($before['numOfFace'] ?? null) ? '(o\'zgarmadi ✓)' : '(!!! O\'ZGARDI !!!)'));

        if ($after['Valid']['enable'] === $enableFlag) {
            $this->info('Natija: yozish MUVAFFAQIYATLI bo\'ldi (kutilgan holatga o\'tdi).');
            $this->line("Eslatma: eski tizim buni qaytarib qo'yishi mumkinmi — buni bilish uchun bir necha daqiqadan/soatdan so'ng shu buyruqni --enable/--disable'siz emas, balki qayta 'searchUserInfo' orqali (yoki shu buyruqni yana ishga tushirib) tekshirib ko'ring.");
        } else {
            $this->error('Natija: yozish KUTILGANDEK ISHLAMADI — Valid.enable o\'zgarmadi. Bu terminal ehtimol to\'liq yozuvni talab qiladi (faqat employeeNo+Valid yetarli emas).');
        }

        if (! $enableFlag) {
            $this->newLine();
            $this->warn("ESLATMA: \"{$employeeNo}\" endi bu terminalda O'CHIRILGAN holatda. Sinovni yakunlagach, quyidagi buyruq bilan ORIGINAL holatga qaytarishni unutmang:");
            $this->line("  php artisan turnstile:test-access-toggle {$employeeNo} --device={$device->name} --enable");
        }

        return self::SUCCESS;
    }
}
