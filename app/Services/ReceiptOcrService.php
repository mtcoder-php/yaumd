<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Throwable;

/**
 * Bank/to'lov cheki suratidan matnni tanib olish (OCR) va undan
 * Universitet hisob raqamiga o'tkazilgan pul summasini ajratib olish
 * xizmati.
 *
 * Serverning o'zida o'rnatiladigan `tesseract` konsol buyrug'idan
 * (tesseract-ocr paketi) foydalanadi — hech qanday tashqi bulut xizmati
 * yoki API kaliti talab qilinmaydi, internetga ulanish shart emas,
 * oylik to'lov yo'q. Symfony Process komponenti Laravel bilan birga
 * allaqachon keladi — composer'da alohida paket o'rnatish shart emas.
 *
 * ====================================================================
 * SERVERGA O'RNATISH (production/serverda BIR MARTA, SSH orqali):
 *
 *   sudo apt-get update
 *   sudo apt-get install -y tesseract-ocr tesseract-ocr-rus tesseract-ocr-uzb
 *
 * ("tesseract-ocr-uzb" ba'zi eski distributivlarda topilmasligi mumkin —
 * shunday holda uni o'tkazib yuborib, faqat "tesseract-ocr tesseract-ocr-rus"
 * bilan cheklansa ham bo'ladi: chek summalari har doim RAQAMDA yozilgani
 * uchun, tilni to'liq bilmasa ham, tesseract raqamlarni yetarlicha yaxshi
 * o'qiydi.)
 *
 * O'rnatilgan-o'rnatilmaganini tekshirish: terminalda `tesseract --version`
 * ====================================================================
 */
class ReceiptOcrService
{
    // Tesseract nima uchun ishlamaganini (agar ishlamasa) shu yerda
    // saqlaymiz — scan() natijasidagi raw_text orqali admin panelida
    // ko'rsatiladi, shunda "nega summa topilmadi" degan savolga darhol
    // (skrinshot yubormasdan) javob topiladi.
    private ?string $lastError = null;

    /**
     * Chek suratidan matnni o'qiydi va topilgan eng ehtimolli summani
     * qaytaradi. Kassir har doim natijani (ayniqsa summani) ko'rib,
     * kerak bo'lsa tuzatib, keyin tasdiqlaydi — OCR 100% xatosiz
     * ishlashga kafolat bermaydi, shuning uchun bu FAQAT yordamchi
     * (summani qo'lda kiritishni tezlashtiruvchi) vosita.
     *
     * @return array{amount: int|null, raw_text: string}
     */
    public function scan(string $absoluteImagePath): array
    {
        $rawText = $this->runTesseract($absoluteImagePath);

        // Tesseract HECH NARSA qaytarmagan bo'lsa (masalan buyruq
        // topilmadi) — bo'sh matn o'rniga aniq sabab ko'rsatiladi, aks
        // holda "OCR o'qigan matnni ko'rish" paneli sukut saqlab,
        // kassirga hech qanday ma'lumot bermas edi.
        if (trim($rawText) === '' && $this->lastError) {
            return [
                'amount'   => null,
                'raw_text' => "[OCR ishlamadi] {$this->lastError}",
            ];
        }

        return [
            'amount'   => $this->extractAmount($rawText),
            'raw_text' => trim($rawText),
        ];
    }

    private function runTesseract(string $imagePath): string
    {
        // Standart holatda oddiy "tesseract" buyrug'i (PATH orqali
        // topiladi) ishlatiladi. Agar PATH orqali topilmasa (masalan
        // ba'zi Windows xizmat/service muhitlari foydalanuvchi
        // darajasidagi PATH o'zgarishlarini ko'rmaydi), config/services.php
        // dagi TESSERACT_BINARY (.env) orqali to'liq yo'l ko'rsatilishi
        // mumkin.
        $binary = config('services.tesseract.binary', 'tesseract');

        // "stdout" — natijani faylga emas, to'g'ridan-to'g'ri javobga
        // chiqarish uchun. --psm 6 — "bir xil bloklardan iborat matn"
        // rejimi, chek/kvitansiya kabi qisqa matnlar uchun mos.
        //
        // MUHIM: "uzb_cyrl" (o'zbekcha, KIRILL yozuvi) ham qo'shildi —
        // ba'zi banklarning cheklari "so'm" o'rniga "сўм"/"сум" kabi
        // kirillcha yozuvda chiqadi, va oddiy "uzb" (lotin) + "rus"
        // kombinatsiyasi buni yetarlicha aniq o'qiy olmagani
        // (natijada summa topilmay qolgani) aniqlandi.
        $output = $this->execute([$binary, $imagePath, 'stdout', '-l', 'uzb+uzb_cyrl+rus+eng', '--psm', '6']);
        if ($output !== null) {
            return $output;
        }

        // Yuqoridagi til paketlaridan biri serverga o'rnatilmagan bo'lishi
        // mumkin — shunda tesseract xato bilan to'xtaydi. tesseract-ocr
        // paketi bilan har doim birga keladigan "eng" bilan qayta
        // urinamiz (chek summasi baribir RAQAMLARDAN iborat).
        return $this->execute([$binary, $imagePath, 'stdout', '-l', 'eng', '--psm', '6']) ?? '';
    }

    private function execute(array $command): ?string
    {
        try {
            $process = new Process($command);
            $process->setTimeout(30);
            $process->run();

            if ($process->isSuccessful()) {
                return $process->getOutput();
            }

            // Xato sababini saqlab qolamiz (masalan "tesseract" buyrug'i
            // PATH'da topilmadi, yoki fayl formatini o'qiy olmadi) —
            // ikkinchi (fallback) urinish ham muvaffaqiyatsiz bo'lsa,
            // shu oxirgi xabar admin panelida ko'rsatiladi.
            $this->lastError = trim($process->getErrorOutput()) ?: "tesseract xato kod bilan tugadi ({$process->getExitCode()})";

            return null;
        } catch (Throwable $e) {
            // "tesseract" binary'si umuman topilmasa (masalan PATH'da
            // yo'q yoki fayl mavjud emas) shu yerga tushadi — tizim
            // qulab tushmaydi, faqat sabab saqlanadi.
            $this->lastError = $e->getMessage();

            return null;
        }
    }

    /**
     * OCR orqali o'qilgan xom matndan pul summasini topishga harakat
     * qiladi. Ikki bosqichli yondashuv:
     *
     *   1) "summa"/"сумма"/"jami"/"to'lov"/"перевод" kabi kalit so'zdan
     *      keyin keladigan birinchi raqamni qidiradi — bu eng ishonchli
     *      usul, chunki chekda summa deyarli har doim shunday kalit
     *      so'z bilan yonma-yon yoziladi.
     *   2) Topilmasa — matndagi barcha "raqam guruhlari"dan (sana va
     *      juda uzun hisob/karta raqamlarini chetlab o'tib) ENG
     *      KATTASINI summa deb hisoblaydi, chunki chekda summa odatda
     *      eng ko'zga tashlanadigan/yirik raqam bo'ladi.
     */
    // Haqiqiy summa deb hisoblanadigan ENG KATTA qiymat — bundan kattaroq
    // "nomzod" hisob raqami/shartnoma kodi/STIR kabi uzun kod bo'lishi
    // ehtimoli baland (haqiqiy to'lovlar odatda bir necha o'n million
    // so'mdan oshmaydi; universitet TO'LIQ shartnoma summasi ham shu
    // chegaradan pastda qoladi). Haqiqiy hayotdagi chekda bundan katta
    // summa chiqsa ham — kassir baribir ko'rib tasdiqlaganidan keyingina
    // saqlanadi, shuning uchun bu faqat "shubhali/keraksiz" raqamlarni
    // (uzun kodlarni) chetlab o'tish uchun ehtiyot chorasi.
    private const MAX_PLAUSIBLE_AMOUNT = 10_000_000_000;

    private function extractAmount(string $text): ?int
    {
        $normalized = str_replace(["\r"], '', $text);

        // "тўлов"/"жами" — o'zbekcha KIRILL yozuvidagi variantlari
        // ("to'lov"/"jami"ning kirillchasi). "aylanma"/"умумий" — rasmiy
        // hisobvaraq-faktura hujjatlarida ("imtiyozli aylanma: ...")
        // to'lov summasi ko'pincha jadvaldan TASHQARIDA, shu so'zdan
        // keyin ALOHIDA va TOZA (jadval "shovqini"siz) chiqadi — shuning
        // uchun bu eng ishonchli manba sifatida qo'shildi.
        $keywordPattern = '/(?:summa|сумма|jami|жами|итого|to\'?lov|тўлов|перевод|amount|платеж|aylanma|айланма|umumiy|умумий)[^\d\n]{0,15}([\d .,]{3,20}\d)/iu';
        if (preg_match($keywordPattern, $normalized, $m)) {
            $amount = $this->normalizeNumber($m[1]);
            if ($amount !== null && $amount >= 1000 && $amount <= self::MAX_PLAUSIBLE_AMOUNT) {
                return $amount;
            }
        }

        // Sana/vaqt yozuvlarini ("20.09.2026", "20.09.2026 14:32") oldindan
        // olib tashlaymiz — aks holda ular bo'sh joy bilan qo'shni raqamga
        // (masalan soatga) "yopishib", yagona uzun raqam sifatida noto'g'ri
        // eng katta summa deb tanlanib qolishi mumkin edi.
        $withoutDates = preg_replace(
            '/\b\d{1,2}[.\/]\d{1,2}[.\/]\d{2,4}(?:[,\s]+\d{1,2}[:.]\d{2}(?:[:.]\d{2})?)?\b/u',
            ' ',
            $normalized
        ) ?? $normalized;

        // MUHIM: guruhlash belgilari orasida FAQAT probel/tab (\s emas!)
        // ishlatiladi — aks holda tesseract turli qatorlardagi ikkita
        // ALOHIDA raqamni (masalan karta raqamining oxiri va keyingi
        // qatordagi summa) bitta "uzun raqam" qilib qo'shib yuborishi
        // mumkin edi (qator ko'chirish belgisi ham \s ga kiradi).
        preg_match_all('/\d[\d \t.,]{2,20}\d|\d{4,}/u', $withoutDates, $matches);

        $candidates = [];
        foreach ($matches[0] as $raw) {
            $amount = $this->normalizeNumber(trim($raw));

            // Juda kichik (1000 so'mdan kam) qiymatlar va MAX_PLAUSIBLE_AMOUNT'dan
            // katta raqamlar (hisob raqami/karta/shartnoma kodi/STIR
            // bo'lishi ehtimoli baland) summa bo'lish ehtimoli past —
            // chetlab o'tiladi.
            if ($amount !== null && $amount >= 1000 && $amount <= self::MAX_PLAUSIBLE_AMOUNT) {
                $candidates[] = $amount;
            }
        }

        if ($candidates === []) {
            return null;
        }

        // ENG KO'P TAKRORLANGAN qiymatni tanlaymiz (max emas!) — rasmiy
        // hisobvaraq-faktura kabi jadvalli hujjatlarda haqiqiy summa
        // odatda bir necha ustunda (masalan "yetkazib berish qiymati",
        // "ЖАМИ", "hisobga olingan holda qiymati") AYNAN TAKRORLANIB
        // chiqadi, shovqinli/tasodifiy kodlar esa faqat bir marta
        // uchraydi. Chastota teng bo'lsa — kattasi tanlanadi.
        $frequency = array_count_values($candidates);
        arsort($frequency);
        $maxFrequency = reset($frequency);
        $topCandidates = array_keys(array_filter($frequency, fn ($count) => $count === $maxFrequency));

        return max($topCandidates);
    }

    /**
     * "1 200 000", "1.200.000", "1,200,000" kabi turli formatdagi
     * yozuvlarni bitta butun songa aylantiradi.
     */
    private function normalizeNumber(string $raw): ?int
    {
        $trimmed = trim($raw);

        // MUHIM: oxiridagi kasr/tiyin qismini ("...,00" yoki "...ю.00" —
        // vergul/nuqtadan keyin ANIQ 1-2 ta raqam) butun qismidan
        // ajratib TASHLAB YUBORAMIZ. Aks holda, masalan, "9 700 000,00"
        // (to'qqiz million yetti yuz ming so'm, ",00" — tiyin/kasr
        // qismi) quyidagi qatordagi oddiy "faqat raqamlarni qoldirish"
        // amali orqali xato ravishda 970 000 000 (YUZ BARAVAR KATTA!)
        // deb o'qilib qolar edi — chunki vergul/nuqta boshqa minglik
        // ajratkichlari (probel) bilan bir xil ko'rib chiqilardi. Minglik
        // guruhi har doim ANIQ 3 ta raqamdan iborat bo'lgani uchun,
        // oxirida 1-2 ta raqam qolishi deyarli har doim KASR qismini
        // bildiradi, minglikni emas.
        $withoutCents = preg_replace('/[.,]\d{1,2}$/', '', $trimmed) ?? $trimmed;

        $digitsOnly = preg_replace('/\D/', '', $withoutCents);

        return ($digitsOnly === '' || $digitsOnly === null) ? null : (int) $digitsOnly;
    }
}
