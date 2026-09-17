<?php

namespace App\Services;

use App\Models\TurnstileDevice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Bitta Hikvision Face ID terminali (DS-K1T671MF va shunga o'xshash access-
 * control terminallar) bilan ISAPI protokoli orqali gaplashadi.
 *
 * IKKI XIL TOIFADAGI METODLAR BOR:
 *   1) O'QISH (fetchAccessControlEvents, searchAcsEvents, searchUserInfo) —
 *      terminalning HECH QANDAY sozlamasini o'zgartirmaydi, shuning uchun
 *      to'liq xavfsiz, eski tizimga (HTTP Listening) hech qanday ta'sir
 *      qilmaydi.
 *   2) YOZISH (setUserValid) — terminaldagi bitta shaxsning kirish
 *      huquqini (Valid.enable) o'zgartiradi. Bu ENDI xavfsiz emas —
 *      eski tizim ham aynan shu maydonni o'zi boshqarayotgan bo'lishi
 *      mumkin (TestTurnstileAccessToggle buyrug'iga qarang — bu metod
 *      HOZIRCHA faqat o'sha qo'lda ishga tushiriladigan sinov buyrug'i
 *      orqali chaqiriladi, avtomatik/rejalashtirilgan hech narsa yo'q).
 *
 * Autentifikatsiya — HTTP Digest (barcha 12 terminalda bir xil admin
 * login/parol, config/services.php'dagi 'hikvision' bo'limidan olinadi).
 */
class HikvisionTerminalClient
{
    /** Bitta so'rovda so'raladigan yozuvlar soni. */
    private const PAGE_SIZE = 30;

    /**
     * Xavfsizlik uchun cheklov — bitta sinxronlash yugurishida bitta
     * terminaldan ko'pi bilan shuncha sahifa o'qiladi (ya'ni ko'pi bilan
     * PAGE_SIZE * MAX_PAGES ta voqea). Agar terminalda bundan ko'p yangi
     * voqea to'planib qolgan bo'lsa (masalan uzoq vaqt sinxronlanmagan
     * bo'lsa), qolgani keyingi yugurishda davom ettiriladi — cheksiz
     * tsikl/xotira muammosining oldini oladi.
     */
    private const MAX_PAGES = 50;

    public function __construct(
        private readonly TurnstileDevice $device,
        private readonly string $username,
        private readonly string $password,
    ) {
    }

    /**
     * $since dan $until gacha bo'lgan barcha "AccessControl" (major=5)
     * voqealarini terminaldan sahifalab o'qib, bitta massivga to'playdi.
     *
     * @return array{events: array<int, array<string, mixed>>, lastEventTime: ?Carbon, reachedCap: bool}
     */
    public function fetchAccessControlEvents(Carbon $since, Carbon $until): array
    {
        $events = [];
        $lastEventTime = null;
        $position = 0;
        $reachedCap = false;

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $response = $this->searchAcsEvents($since, $until, $position, self::PAGE_SIZE);
            $body = $response['AcsEvent'] ?? null;

            if (! is_array($body)) {
                break;
            }

            $infoList = $body['InfoList'] ?? [];

            foreach ($infoList as $row) {
                if (($row['major'] ?? null) !== 5) {
                    // Hozircha faqat AccessControl (major=5) voqealari bilan
                    // qiziqamiz — major=3 kabi davriy "tizim ishlayapti"
                    // signallarini o'tkazib yuboramiz.
                    continue;
                }

                $events[] = $row;

                if (isset($row['time'])) {
                    // MUHIM: terminal vaqtni "+05:00" (Toshkent) belgisi bilan
                    // yuboradi. Carbon::parse() to'g'ri lahzani o'qiydi, lekin
                    // "+05:00" belgisini o'zida saqlab qoladi — agar shu
                    // holatda saqlansa, Eloquent'ning 'datetime' cast'i uni
                    // avval UTC'ga aylantirmasdan, xuddi shu raqamlarni
                    // (masalan "17:00:20") UTC sifatida bazaga yozib qo'yadi.
                    // Natijada keyingi o'qishda bu "kelajakdagi" vaqt bo'lib
                    // chiqadi (haqiqiy UTC'dan ~5 soat oldinda). ->utc() shu
                    // aylantirishni majburan bajaradi.
                    $eventTime = Carbon::parse($row['time'])->utc();
                    if (! $lastEventTime || $eventTime->greaterThan($lastEventTime)) {
                        $lastEventTime = $eventTime;
                    }
                }
            }

            $status = $body['responseStatusStrg'] ?? 'OK';
            $numOfMatches = (int) ($body['numOfMatches'] ?? 0);

            if ($status !== 'MORE' || $numOfMatches === 0) {
                break;
            }

            $position += $numOfMatches;

            if ($page === self::MAX_PAGES - 1) {
                $reachedCap = true;
            }
        }

        return [
            'events' => $events,
            'lastEventTime' => $lastEventTime,
            'reachedCap' => $reachedCap,
        ];
    }

    /**
     * ISAPI'ning /ISAPI/AccessControl/AcsEvent manziliga bitta sahifalik
     * qidiruv so'rovini yuboradi.
     *
     * @return array<string, mixed>
     */
    public function searchAcsEvents(Carbon $startTime, Carbon $endTime, int $position, int $maxResults): array
    {
        $url = $this->device->baseUrl() . '/ISAPI/AccessControl/AcsEvent?format=json';

        // MUHIM: retry()'ning standart xatti-harakati — barcha urinishlar
        // muvaffaqiyatsiz tugasa, o'zi avtomatik ravishda Laravel'ning
        // RequestException'ini tashlaydi, va bu xatoning matni Guzzle
        // tomonidan atigi ~120 belgigacha KESIB TASHLANADI (diagnostika
        // uchun deyarli foydasiz). "throw: false" shu avtomatik xatoni
        // o'chiradi — shunda pastdagi $response->failed() tekshiruvi ishga
        // tushadi va to'liq (kesilmagan) $response->body() ko'rsatiladi.
        $response = Http::withDigestAuth($this->username, $this->password)
            ->timeout(20)
            ->retry(2, 500, throw: false)
            ->post($url, [
                'AcsEventCond' => [
                    'searchID' => (string) str()->uuid(),
                    'searchResultPosition' => $position,
                    'maxResults' => $maxResults,
                    'major' => 5,
                    'minor' => 0,
                    'startTime' => $startTime->toIso8601String(),
                    'endTime' => $endTime->toIso8601String(),
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "Hikvision ISAPI so'rovi muvaffaqiyatsiz ({$this->device->name}, HTTP {$response->status()}): "
                . $response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Terminaldagi bitta shaxsning to'liq yozuvini (ism, Valid davri,
     * doorRight, yuz soni va h.k.) employeeNo bo'yicha qidiradi — FAQAT
     * O'QISH, hech narsani o'zgartirmaydi.
     *
     * @return array<string, mixed>|null Topilmasa — null.
     */
    public function searchUserInfo(string $employeeNo): ?array
    {
        $url = $this->device->baseUrl() . '/ISAPI/AccessControl/UserInfo/Search?format=json';

        $response = Http::withDigestAuth($this->username, $this->password)
            ->timeout(20)
            ->retry(2, 500, throw: false)
            ->post($url, [
                'UserInfoSearchCond' => [
                    'searchID' => (string) str()->uuid(),
                    'searchResultPosition' => 0,
                    'maxResults' => 1,
                    'EmployeeNoList' => [
                        ['employeeNo' => $employeeNo],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "Hikvision ISAPI so'rovi muvaffaqiyatsiz ({$this->device->name}, HTTP {$response->status()}): "
                . $response->body()
            );
        }

        $body = $response->json('UserInfoSearch') ?? [];
        $list = $body['UserInfo'] ?? [];

        return $list[0] ?? null;
    }

    /**
     * Bitta shaxsning "Valid.enable" (kirish huquqi yoqilgan/o'chirilgan)
     * holatini o'zgartiradi.
     *
     * MUHIM XAVFSIZLIK QARORI: bu yerda ATAYLAB $currentValid'dan faqat
     * 'beginTime'/'endTime'/'timeType'ni saqlab qolib, YANGI so'rovga
     * FAQAT 'employeeNo' + 'Valid' yuboriladi — boshqa hech qanday maydon
     * (ism, karta, yuz ma'lumoti va h.k.) qo'shilmaydi. Sabab: Hikvision
     * ISAPI'ning UserInfo/Modify'i qisman (faqat yuborilgan maydonlarni)
     * yangilashni qo'llab-quvvatlaydi — agar bo'lmagan maydonlarni ham
     * (masalan GET'dan olingan butun yozuvni) qaytarib yuborsak va
     * ulardan biri (masalan 'faceURL' — bu READ-ONLY, hisoblab chiqarilgan
     * maydon) noto'g'ri bo'lsa, terminal butun so'rovni rad etishi yoki,
     * eng yomoni, shaxsning yuz ma'lumotini buzib qo'yishi mumkin. Minimal
     * so'rov — eng kam xavfli yo'l. Agar terminal shu minimal so'rovni
     * rad etsa (masalan "kerakli maydon yo'q" xatosi), demak bu firmware
     * to'liq yozuvni talab qiladi — bu holni TestTurnstileAccessToggle
     * buyrug'i orqali ANIQ shu terminalda sinab bilib olamiz.
     *
     * @param array<string, mixed> $currentValid searchUserInfo() natijasidagi 'Valid' massivi
     * @return array<string, mixed> Terminal javobi (statusCode/statusString)
     */
    public function setUserValid(string $employeeNo, array $currentValid, bool $enabled): array
    {
        $url = $this->device->baseUrl() . '/ISAPI/AccessControl/UserInfo/Modify?format=json';

        $payload = [
            'UserInfo' => [
                'employeeNo' => $employeeNo,
                'Valid' => [
                    'enable' => $enabled,
                    'beginTime' => $currentValid['beginTime'] ?? '2000-01-01T00:00:00',
                    'endTime' => $currentValid['endTime'] ?? '2037-12-31T23:59:59',
                    'timeType' => $currentValid['timeType'] ?? 'local',
                ],
            ],
        ];

        $response = Http::withDigestAuth($this->username, $this->password)
            ->timeout(20)
            ->retry(1, 500, throw: false)
            ->put($url, $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                "Hikvision ISAPI UserInfo/Modify muvaffaqiyatsiz ({$this->device->name}, HTTP {$response->status()}): "
                . $response->body()
            );
        }

        return $response->json() ?? [];
    }
}
