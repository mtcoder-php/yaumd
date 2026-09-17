<?php

namespace App\Console\Commands;

use App\Services\TurnstileAccessDecisionService;
use Illuminate\Console\Command;

/**
 * TO'LIQ XAVFSIZ — hech qanday terminalga ULANMAYDI, hech narsaga
 * YOZMAYDI. Faqat bazadagi ma'lumot (kontrakt to'lovlari, PersonMatch)
 * asosida, har bir moslashtirilgan talaba uchun turniketda Valid.enable
 * QANDAY BO'LISHI KERAKLIGINI hisoblab, jadval ko'rinishida chiqaradi.
 *
 * Bu — Phase 4'ning "tayyorgarlik" bosqichi: 1-oktabrdan keyin (birinchi
 * haqiqiy qarzdorlar paydo bo'lgach) shu ro'yxatni ko'rib, keyin xavfsiz
 * ravishda haqiqiy yozishga (terminalga) o'tamiz.
 */
class PreviewTurnstileAccessDecisions extends Command
{
    protected $signature = 'turnstile:access-preview {--only-changes : Faqat "yopilishi kerak" (is_compliant=false) bo\'lganlarni ko\'rsatish}';

    protected $description = "(DRY-RUN, YOZMAYDI) Har bir moslashtirilgan talaba uchun turniket kirish huquqi qanday bo'lishi kerakligini ko'rsatadi";

    public function handle(TurnstileAccessDecisionService $service): int
    {
        $decisions = $service->decisionsForStudents();

        if ($decisions->isEmpty()) {
            $this->warn("Hali hech qanday talaba moslashtirilmagan — avval 'turnstile:match-people' ishga tushiring.");

            return self::SUCCESS;
        }

        $unresolved = $decisions->filter(fn (array $d) => $d['desired_enable'] === null);
        $resolved = $decisions->filter(fn (array $d) => $d['desired_enable'] !== null);

        $toShow = $this->option('only-changes')
            ? $resolved->filter(fn (array $d) => $d['desired_enable'] === false)
            : $resolved;

        $rows = $toShow
            ->sortBy(fn (array $d) => $d['desired_enable'] ? 1 : 0)
            ->map(fn (array $d) => [
                $d['employee_no'],
                $d['name'],
                $d['desired_enable'] ? '✅ OCHIQ' : '⛔ YOPIQ',
                $d['reason'] ?? '—',
                $d['debt_amount'] !== null ? number_format($d['debt_amount'], 0, '.', ' ') . " so'm" : '—',
            ]);

        $this->table(['employeeNo', 'Ism', 'Bo\'lishi kerak', 'Sabab', 'Qarz'], $rows);

        $enableCount = $resolved->where('desired_enable', true)->count();
        $disableCount = $resolved->where('desired_enable', false)->count();

        $this->newLine();
        $this->info("Jami moslashtirilgan talaba: {$decisions->count()}. Ochiq bo'lishi kerak: {$enableCount}. Yopiq bo'lishi kerak: {$disableCount}.");

        if ($unresolved->isNotEmpty()) {
            $this->warn("{$unresolved->count()} ta talaba hal qilinmagan holatda (masalan akademik ta'tilda/chetlashtirilgan) — bular avtomatik qarorga kiritilmaydi, alohida ko'rib chiqiladi.");
        }

        $this->newLine();
        $this->comment("Eslatma: bu FAQAT ko'rsatish — hech narsa terminalga yozilmadi. Haqiqiy yozish hali qo'shilmagan (eski tizim bilan to'qnashuv tekshirilgach qo'shiladi).");

        return self::SUCCESS;
    }
}
