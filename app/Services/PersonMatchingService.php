<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;

/**
 * Turniket terminalidan kelgan ism (masalan "KAMOLA (Tinchlik) SHUKUROVA"
 * yoki "ZUXRAXON MUZRABBEKOVA AVAZBEKOVNA") bilan YAUMD bazasidagi talaba/
 * xodim yozuvini ISM BO'YICHA (tokenlarga bo'lib) solishtiradi.
 *
 * NEGA ANIQ TENGLIK EMAS: eski CRM operatorlari ismlarni har xil formatda
 * kiritgan — katta/kichik harf, qavs ichidagi qo'shimcha so'z, ortiqcha
 * bo'shliq, va ba'zan otasining ismi (patronim) umuman yozilmagan. Shu
 * sababli aniq satr solishtirish deyarli hech qachon mos kelmaydi.
 *
 * ALGORITM (oddiy, tushunarli, keyinchalik sozlash mumkin):
 *   1. Ikkala ism ham "tokenlar to'plami"ga aylantiriladi (normalizeTokens).
 *   2. Har bir nomzod uchun "score" = terminal ismidagi nechta so'z
 *      nomzod ismida ham topilgani / terminal ismidagi jami so'zlar soni.
 *      (Terminal ismi ko'pincha QISQAROQ — masalan patronim yo'q — shuning
 *      uchun "terminal so'zlari nomzodda topildimi" ko'proq ishonchli
 *      mezon, aksincha emas.)
 *   3. score=1.0 (barcha so'zlar topildi) VA kamida 2 ta so'z mos keldi
 *      VA eng yaxshi nomzod boshqalardan ANIQ ustun bo'lsa — 'auto_matched'.
 *      Aks holda, kamida bitta ehtimoliy nomzod bo'lsa — 'needs_review'
 *      (admin panelida ko'rib chiqiladi). Hech narsa topilmasa —
 *      'unmatched'.
 */
class PersonMatchingService
{
    /** Avtomatik tasdiqlash uchun minimal mos kelgan so'zlar soni. */
    private const MIN_MATCHED_TOKENS_FOR_AUTO = 2;

    /** Bu darajadan past bo'lgan nomzodlar "needs_review"ga ham kirmaydi. */
    private const MIN_SCORE_FOR_REVIEW = 0.4;

    /**
     * Ismni "TOKEN1 TOKEN2 ..." ko'rinishidagi massivga aylantiradi:
     * qavs ichidagi matn olib tashlanadi, katta harfga o'tkaziladi,
     * harf/bo'shliqdan boshqa hamma narsa (tinish belgilari, raqamlar)
     * olib tashlanadi, ortiqcha bo'shliqlar yig'ishtiriladi.
     *
     * @return array<int, string>
     */
    public function normalizeTokens(?string $name): array
    {
        if ($name === null || trim($name) === '') {
            return [];
        }

        $name = preg_replace('/\(.*?\)/u', ' ', $name) ?? $name;
        $name = mb_strtoupper($name, 'UTF-8');
        $name = preg_replace('/[^\p{L}\s]/u', ' ', $name) ?? $name;
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);

        if ($name === '') {
            return [];
        }

        return explode(' ', $name);
    }

    /**
     * Barcha talabalarni oldindan "id + to'liq ism + tokenlar" shaklida
     * bitta massivga yig'ib oladi — shunda har bir employee_no uchun
     * qaytadan bazaga so'rov yubormay, xotiradagi shu ro'yxat bilan
     * solishtiriladi (bir nechta yuzlab employee_no bo'lsa ham tez
     * ishlaydi).
     *
     * @return array<int, array{id:int, name:string, tokens:array<int,string>}>
     */
    public function buildStudentPool(): array
    {
        return Student::query()
            ->select('id', 'first_name', 'last_name', 'middle_name')
            ->get()
            ->map(function (Student $student) {
                $fullName = trim("{$student->last_name} {$student->first_name} {$student->middle_name}");

                return [
                    'id' => $student->id,
                    'name' => $fullName,
                    'tokens' => $this->normalizeTokens($fullName),
                ];
            })
            ->all();
    }

    /**
     * Xodimlar (o'qituvchi/xodim/admin) — 'users' jadvalidan, 'student'
     * rolidagi (ya'ni faqat talaba portali kirish uchun yaratilgan)
     * hisoblar chiqarib tashlanadi. Rol umuman tayinlanmagan foydalanuvchi
     * (masalan yangi qo'shilgan xodim) ham xavfsizlik uchun ro'yxatda
     * QOLDIRILADI — chunki u ham haqiqiy xodim bo'lishi mumkin.
     *
     * @return array<int, array{id:int, name:string, tokens:array<int,string>}>
     */
    public function buildStaffPool(): array
    {
        return User::query()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'))
            ->select('id', 'full_name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->full_name,
                'tokens' => $this->normalizeTokens($user->full_name),
            ])
            ->all();
    }

    /**
     * $pool ichidan $rawName'ga eng mos nomzodlarni topadi (eng yaxshi
     * ko'pi bilan 5 tasi, ballga qarab kamayish tartibida).
     *
     * @param array<int, array{id:int, name:string, tokens:array<int,string>}> $pool
     * @return array{candidates: array<int, array{id:int, name:string, score:float, matched_tokens:int}>, best: ?array, is_unambiguous: bool}
     */
    public function findCandidates(array $pool, ?string $rawName): array
    {
        $terminalTokens = $this->normalizeTokens($rawName);

        if (empty($terminalTokens) || empty($pool)) {
            return ['candidates' => [], 'best' => null, 'is_unambiguous' => false];
        }

        $candidates = [];

        foreach ($pool as $entry) {
            if (empty($entry['tokens'])) {
                continue;
            }

            $matched = count(array_intersect($terminalTokens, $entry['tokens']));

            if ($matched === 0) {
                continue;
            }

            $candidates[] = [
                'id' => $entry['id'],
                'name' => $entry['name'],
                'score' => round($matched / count($terminalTokens), 4),
                'matched_tokens' => $matched,
            ];
        }

        usort($candidates, function (array $a, array $b) {
            return $b['score'] <=> $a['score'] ?: $b['matched_tokens'] <=> $a['matched_tokens'];
        });

        $top = array_slice($candidates, 0, 5);
        $best = $top[0] ?? null;
        $second = $top[1] ?? null;

        // "Aniq" hisoblanishi uchun eng yaxshi nomzod ikkinchisidan yuqori
        // bo'lishi (yoki ikkinchisi umuman bo'lmasligi) shart — aks holda
        // ikkita bir xil ismli odam bo'lishi mumkin, shuning uchun admin
        // ko'rib chiqishi kerak.
        $isUnambiguous = $best !== null && (
                $second === null
                || $best['score'] > $second['score']
                || $best['matched_tokens'] > $second['matched_tokens']
            );

        return [
            'candidates' => $top,
            'best' => $best,
            'is_unambiguous' => $isUnambiguous,
        ];
    }

    /**
     * Topilgan eng yaxshi nomzod asosida qanday holat (status)
     * belgilanishi kerakligini hal qiladi.
     */
    public function decideStatus(?array $best, bool $isUnambiguous): string
    {
        if ($best === null) {
            return \App\Models\PersonMatch::STATUS_UNMATCHED;
        }

        if (
            $best['score'] >= 1.0
            && $best['matched_tokens'] >= self::MIN_MATCHED_TOKENS_FOR_AUTO
            && $isUnambiguous
        ) {
            return \App\Models\PersonMatch::STATUS_AUTO_MATCHED;
        }

        if ($best['score'] >= self::MIN_SCORE_FOR_REVIEW) {
            return \App\Models\PersonMatch::STATUS_NEEDS_REVIEW;
        }

        return \App\Models\PersonMatch::STATUS_UNMATCHED;
    }
}
