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
 * Hozircha faqat "voqealar tarixini o'qish" (AcsEvent qidiruv) ishlatiladi —
 * bu terminalning HECH QANDAY sozlamasini o'zgartirmaydi (faqat o'qish),
 * shuning uchun terminalning HTTP Listening orqali eski tizimga xabar
 * yuborishiga umuman ta'sir qilmaydi (parallel, xavfsiz).
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
}
