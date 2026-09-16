<?php

namespace App\Console\Commands;

use App\Models\PersonMatch;
use App\Models\TurnstileEvent;
use App\Services\PersonMatchingService;
use Illuminate\Console\Command;

/**
 * 'turnstile_events' jadvalidagi barcha noyob employee_no'larni (masalan
 * "s6236", "u596") YAUMD'dagi talaba/xodim yozuvlari bilan ism bo'yicha
 * moslashtirib, 'person_matches' jadvaliga yozadi.
 *
 * Bu buyruq necha marta ishga tushirilsa ham xavfsiz (idempotent):
 * admin allaqachon qo'lda tasdiqlagan ('matched') yoki ataylab rad etgan
 * ('rejected') yozuvlarga --force berilmasa tegilmaydi — faqat yangi
 * paydo bo'lgan yoki hali hal qilinmagan ('needs_review'/'unmatched'/
 * 'auto_matched') employee_no'lar qayta tekshiriladi.
 */
class MatchTurnstilePeople extends Command
{
    protected $signature = 'turnstile:match-people {--force : matched/rejected holatidagilarni ham qayta tekshirish}';

    protected $description = "Turniket voqealaridagi employeeNo'larni talaba/xodim yozuvlari bilan ism bo'yicha moslashtiradi";

    public function handle(PersonMatchingService $service): int
    {
        $pairs = TurnstileEvent::query()
            ->whereNotNull('employee_no')
            ->where('employee_no', '!=', '')
            ->selectRaw('employee_no, MAX(person_name) as person_name')
            ->groupBy('employee_no')
            ->get();

        if ($pairs->isEmpty()) {
            $this->warn("Hali hech qanday turniket voqeasida employee_no yo'q — avval 'turnstile:sync-events' ishga tushirilganini tekshiring.");

            return self::SUCCESS;
        }

        $existingStatuses = PersonMatch::query()->pluck('status', 'employee_no');
        $force = (bool) $this->option('force');

        $this->info('Talaba/xodim ro\'yxatlari xotiraga yuklanmoqda...');
        $studentPool = $service->buildStudentPool();
        $staffPool = $service->buildStaffPool();
        $this->info(sprintf('  %d talaba, %d xodim yuklandi.', count($studentPool), count($staffPool)));

        $stats = ['auto' => 0, 'review' => 0, 'unmatched' => 0, 'skipped' => 0];

        foreach ($pairs as $pair) {
            $employeeNo = $pair->employee_no;
            $currentStatus = $existingStatuses[$employeeNo] ?? null;

            if (! $force && in_array($currentStatus, [PersonMatch::STATUS_MATCHED, PersonMatch::STATUS_REJECTED], true)) {
                $stats['skipped']++;

                continue;
            }

            $prefix = mb_strtolower(mb_substr($employeeNo, 0, 1));

            [$pool, $matchableType] = match ($prefix) {
                's' => [$studentPool, 'student'],
                'u' => [$staffPool, 'staff'],
                default => [null, null],
            };

            $rawName = $pair->person_name;

            if ($pool === null) {
                // Noma'lum prefiks — kelajakda boshqa turdagi kod paydo
                // bo'lsa ham, dastur qulamasin, shunchaki "unmatched"
                // sifatida belgilab, admin qo'lda ko'rib chiqsin.
                $match = PersonMatch::updateOrCreate(
                    ['employee_no' => $employeeNo],
                    [
                        'person_name' => $rawName,
                        'matchable_type' => null,
                        'matchable_id' => null,
                        'status' => PersonMatch::STATUS_UNMATCHED,
                        'confidence' => null,
                        'candidates' => [],
                    ]
                );
                $match->syncEventsMatch();
                $stats['unmatched']++;

                continue;
            }

            $result = $service->findCandidates($pool, $rawName);
            $status = $service->decideStatus($result['best'], $result['is_unambiguous']);

            $candidatesForJson = array_map(fn (array $c) => [
                'type' => $matchableType,
                'id' => $c['id'],
                'name' => $c['name'],
                'score' => $c['score'],
            ], $result['candidates']);

            $attributes = [
                'person_name' => $rawName,
                'status' => $status,
                'candidates' => $candidatesForJson,
                'matchable_type' => null,
                'matchable_id' => null,
                'confidence' => $result['best']['score'] ?? null,
            ];

            if ($status === PersonMatch::STATUS_AUTO_MATCHED) {
                $attributes['matchable_type'] = $matchableType;
                $attributes['matchable_id'] = $result['best']['id'];
                $stats['auto']++;
            } elseif ($status === PersonMatch::STATUS_NEEDS_REVIEW) {
                $stats['review']++;
            } else {
                $stats['unmatched']++;
            }

            $match = PersonMatch::updateOrCreate(['employee_no' => $employeeNo], $attributes);
            $match->syncEventsMatch();
        }

        $this->newLine();
        $this->info(sprintf(
            "Avtomatik moslashtirildi: %d, ko'rib chiqish kerak: %d, topilmadi: %d, o'zgarishsiz qoldirildi (allaqachon hal qilingan): %d.",
            $stats['auto'],
            $stats['review'],
            $stats['unmatched'],
            $stats['skipped']
        ));

        if ($stats['review'] > 0 || $stats['unmatched'] > 0) {
            $this->line("Admin panel: /admin/turnstile/matches — ko'rib chiqish/topilmadi holatlarini qo'lda hal qiling.");
        }

        return self::SUCCESS;
    }
}
