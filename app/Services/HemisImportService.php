<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Contract;
use App\Models\Direction;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * HEMIS'dan eksport qilingan talabalar ro'yxatini (Excel) import qilish.
 *
 * Ataylab ikki qatlamga bo'lingan:
 *  - readRows()   — faylni PhpSpreadsheet bilan o'qib, xom 2D massivga aylantiradi (yupqa qatlam).
 *  - importRows() — asosiy biznes mantiq: ustunlarni moslashtirish, qiymatlarni
 *                    tekshirish/normallashtirish va bazaga yozish (sof PHP massiv bilan ishlaydi,
 *                    shu sababli PhpSpreadsheet'siz ham alohida test qilish mumkin).
 */
class HemisImportService
{
    /**
     * Qabul qilinadigan ustun sarlavhalari (kalit => shu kalitga mos keladigan barcha variantlar).
     * Solishtirish paytida harflar kichraytiriladi, bo'sh joylar va tutuq belgisi variantlari olib tashlanadi.
     */
    private const HEADER_ALIASES = [
        'hemis_id'        => ['hemis id', 'hemisid', 'talaba id'],
        'student_number'  => ['talaba raqami', 'student number', 'id raqami', 'raqami'],
        'last_name'       => ['familiya', 'familiyasi'],
        'first_name'      => ['ism', 'ismi'],
        'middle_name'     => ['sharifi', 'otasining ismi', 'otasini ismi'],
        'jshshir'         => ['jshshir', 'jshir'],
        'passport_series' => ['passport', 'passport seriya', 'passport seriya va raqami'],
        'birth_date'      => ['tugilgan sana', 'tugilgan kun'],
        'gender'          => ['jinsi', 'jins'],
        'direction_code'  => ['yonalish kodi', 'hemis kodi', 'yonalish hemis kodi'],
        'degree'          => ['talim darajasi', 'daraja'],
        'study_form'      => ['talim shakli'],
        'course_year'     => ['kurs', 'kurs yili'],
        'phone'           => ['telefon', 'tel'],
        'email'           => ['email', 'elektron pochta'],
        'address'         => ['manzil', 'yashash manzili'],
        'funding_type'    => ['moliyalashtirish turi', 'moliyalashtirish', 'toifasi', 'grant yoki kontrakt'],
    ];

    private const GENDER_MAP = [
        'erkak' => 'male', 'male' => 'male', 'm' => 'male',
        'ayol'  => 'female', 'female' => 'female', 'f' => 'female', 'a' => 'female',
    ];

    private const DEGREE_MAP = [
        'bakalavr' => 'bachelor', 'bachelor' => 'bachelor',
        'magistr'  => 'master', 'master' => 'master',
    ];

    private const STUDY_FORM_MAP = [
        'kunduzgi' => 'full_time', 'full_time' => 'full_time', 'kunduzgi talim' => 'full_time',
        'kechki'   => 'evening', 'evening' => 'evening',
        'sirtqi'   => 'distance', 'distance' => 'distance',
    ];

    private const FUNDING_MAP = [
        'grant'    => 'grant',
        'kontrakt' => 'contract', 'contract' => 'contract', 'pullik' => 'contract',
    ];

    /**
     * Import uchun namuna (shablon) xlsx faylini bayt (string) shaklida qaytaradi.
     */
    public function template(): string
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Talabalar');

        $headers = [
            'HEMIS ID', 'Talaba raqami', 'Familiya', 'Ism', 'Sharifi',
            'JSHSHIR', 'Passport seriya va raqami', "Tug'ilgan sana", 'Jinsi',
            "Yo'nalish kodi", "Ta'lim darajasi", "Ta'lim shakli", 'Kurs',
            'Telefon', 'Email', 'Manzil', 'Moliyalashtirish turi',
        ];
        foreach ($headers as $col => $title) {
            $sheet->setCellValue([$col + 1, 1], $title);
        }

        $example = [
            '20230001', 'BK000000001', 'Aliyev', 'Vali', 'Valiyevich',
            '30101950000000', 'AB1234567', '15.03.2005', 'Erkak',
            '60110900', 'Bakalavr', 'Kunduzgi', '1',
            '+998901234567', 'vali@example.com', 'Toshkent sh.', 'Kontrakt',
        ];
        foreach ($example as $col => $value) {
            $sheet->setCellValue([$col + 1, 2], $value);
        }

        $help = $spreadsheet->createSheet();
        $help->setTitle("Yo'riqnoma");
        $help->setCellValue('A1', 'Qabul qilinadigan qiymatlar:');
        $help->setCellValue('A2', 'Jinsi: Erkak / Ayol');
        $help->setCellValue('A3', "Ta'lim darajasi: Bakalavr / Magistr");
        $help->setCellValue('A4', "Ta'lim shakli: Kunduzgi / Kechki / Sirtqi");
        $help->setCellValue('A5', "Yo'nalish kodi — Fakultetlar/Yo'nalishlar bo'limidagi HEMIS kodi bilan bir xil bo'lishi shart.");
        $help->setCellValue('A6', "HEMIS ID yoki Talaba raqamidan kamida bittasi to'ldirilishi shart (qayta yuklashda shu bo'yicha yangilanadi).");
        $help->setCellValue('A7', "Moliyalashtirish turi: Grant / Kontrakt (bo'sh qoldirilsa — Kontrakt deb olinadi). \"Kontrakt\" tanlansa, shu talaba uchun avtomatik kontrakt shartnoma yaratiladi.");

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Yuklangan faylni o'qib, xom qator massiviga aylantiradi.
     * Birinchi qator — sarlavhalar deb qabul qilinadi.
     *
     * @return array<int, array<int, mixed>>
     */
    public function readRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, false);
    }

    public function import(UploadedFile $file, int $academicYearId): array
    {
        return $this->importRows($this->readRows($file), $academicYearId);
    }

    /**
     * Asosiy import mantiqi — sof PHP massiv bilan ishlaydi, shu sababli
     * PhpSpreadsheet'siz ham to'g'ridan-to'g'ri test qilinishi mumkin.
     *
     * @param  array<int, array<int, mixed>>  $rows  Birinchi qator sarlavhalar
     * @return array{created:int, updated:int, skipped:int, errors:array<int,string>}
     */
    public function importRows(array $rows, int $academicYearId): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        if (empty($rows)) {
            return $result;
        }

        if (! AcademicYear::whereKey($academicYearId)->exists()) {
            $result['errors'][] = "O'quv yili topilmadi";
            return $result;
        }

        $headerRow = array_shift($rows);
        $columnMap = $this->mapHeaders($headerRow);

        $directionCache = [];

        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2; // 1-qator sarlavha, massiv 0-dan boshlanadi

            if ($this->isRowEmpty($row)) {
                continue;
            }

            $data = $this->extractRowData($row, $columnMap);

            $hemisId       = $data['hemis_id'] ?: null;
            $studentNumber = $data['student_number'] ?: null;

            if (! $hemisId && ! $studentNumber) {
                $result['errors'][] = "{$rowNumber}-qator: HEMIS ID yoki Talaba raqami bo'sh";
                $result['skipped']++;
                continue;
            }

            if (empty($data['first_name']) || empty($data['last_name'])) {
                $result['errors'][] = "{$rowNumber}-qator: Ism yoki Familiya bo'sh";
                $result['skipped']++;
                continue;
            }

            $directionCode = trim((string) ($data['direction_code'] ?? ''));
            if ($directionCode === '') {
                $result['errors'][] = "{$rowNumber}-qator: Yo'nalish kodi bo'sh";
                $result['skipped']++;
                continue;
            }

            if (! array_key_exists($directionCode, $directionCache)) {
                $directionCache[$directionCode] = Direction::where('hemis_code', $directionCode)->value('id');
            }
            $directionId = $directionCache[$directionCode];

            if (! $directionId) {
                $result['errors'][] = "{$rowNumber}-qator: Yo'nalish kodi topilmadi ({$directionCode})";
                $result['skipped']++;
                continue;
            }

            [$birthDay, $birthMonth, $birthYear] = $this->parseBirthDate($data['birth_date'] ?? null);

            $attributes = [
                'academic_year_id' => $academicYearId,
                'direction_id'     => $directionId,
                'hemis_id'         => $hemisId,
                'student_number'   => $studentNumber,
                'first_name'       => trim((string) $data['first_name']),
                'last_name'        => trim((string) $data['last_name']),
                'middle_name'      => $data['middle_name'] ? trim((string) $data['middle_name']) : null,
                'passport_series'  => $data['passport_series'] ?: null,
                'jshshir'          => $data['jshshir'] ?: null,
                'phone'            => $data['phone'] ?: null,
                'email'            => $data['email'] ?: null,
                'birth_day'        => $birthDay,
                'birth_month'      => $birthMonth,
                'birth_year'       => $birthYear,
                'gender'           => $this->mapGender($data['gender'] ?? null),
                'degree'           => $this->mapDegree($data['degree'] ?? null),
                'study_form'       => $this->mapStudyForm($data['study_form'] ?? null),
                'course_year'      => $data['course_year'] !== '' && $data['course_year'] !== null ? (int) $data['course_year'] : 1,
                'status'           => 'active',
                'funding_type'     => $this->mapFundingType($data['funding_type'] ?? null),
                'address'          => $data['address'] ?: null,
            ];

            try {
                DB::transaction(function () use ($hemisId, $studentNumber, $attributes, &$result) {
                    $query = Student::query();
                    if ($hemisId) {
                        $query->where('hemis_id', $hemisId);
                    } else {
                        $query->where('student_number', $studentNumber);
                    }

                    $student = $query->first();

                    if ($student) {
                        $student->update($attributes);
                        $result['updated']++;
                    } else {
                        $student = Student::create($attributes);
                        $result['created']++;
                    }

                    // Kontrakt asosida import qilingan, lekin hali kontrakti
                    // bo'lmagan talaba uchun avtomatik kontrakt yaratamiz
                    // (Abituriyentlar oqimidan kelmagani uchun applicant_id
                    // emas, student_id orqali bog'lanadi).
                    if ($student->funding_type === 'contract' && ! Contract::where('student_id', $student->id)->exists()) {
                        Contract::create([
                            'student_id'      => $student->id,
                            'direction_id'    => $student->direction_id,
                            'contract_number' => Contract::generateNumber(),
                            'amount'          => $student->direction?->annual_fee ?? 0,
                            'payment_type'    => 'contract',
                            'status'          => 'draft',
                        ]);
                    }
                });
            } catch (\Throwable $e) {
                $result['errors'][] = "{$rowNumber}-qator: bazaga yozishda xatolik ({$e->getMessage()})";
                $result['skipped']++;
            }
        }

        return $result;
    }

    /**
     * Sarlavha qatoridan {ustun_indeksi => ichki_kalit} moslamasini quradi.
     *
     * @return array<int, string>
     */
    private function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $rawHeader) {
            $normalized = $this->normalizeHeader((string) $rawHeader);

            foreach (self::HEADER_ALIASES as $key => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$index] = $key;
                    break;
                }
            }
        }

        return $map;
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        // tutuq belgisining turli variantlarini olib tashlash: ' ’ ‘ ʻ
        $value = str_replace(["'", '’', '‘', 'ʻ', 'ʼ'], '', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractRowData(array $row, array $columnMap): array
    {
        $data = array_fill_keys(array_keys(self::HEADER_ALIASES), null);

        foreach ($columnMap as $index => $key) {
            $value = $row[$index] ?? null;
            $data[$key] = is_string($value) ? trim($value) : $value;
        }

        return $data;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function mapGender(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return self::GENDER_MAP[mb_strtolower(trim($value))] ?? null;
    }

    private function mapDegree(?string $value): string
    {
        if (! $value) {
            return 'bachelor';
        }

        return self::DEGREE_MAP[mb_strtolower(trim($value))] ?? 'bachelor';
    }

    private function mapStudyForm(?string $value): string
    {
        if (! $value) {
            return 'full_time';
        }

        return self::STUDY_FORM_MAP[mb_strtolower(trim($value))] ?? 'full_time';
    }

    private function mapFundingType(?string $value): string
    {
        if (! $value) {
            return 'contract';
        }

        return self::FUNDING_MAP[mb_strtolower(trim($value))] ?? 'contract';
    }

    /**
     * "15.03.2005", "15/03/2005" yoki "2005-03-15" formatlarini qabul qiladi.
     *
     * @return array{0: ?int, 1: ?int, 2: ?int}
     */
    private function parseBirthDate(mixed $value): array
    {
        if (! $value) {
            return [null, null, null];
        }

        $value = trim((string) $value);

        if (preg_match('~^(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{4})$~', $value, $m)) {
            return [(int) $m[1], (int) $m[2], (int) $m[3]];
        }

        if (preg_match('~^(\d{4})[.\-/](\d{1,2})[.\-/](\d{1,2})$~', $value, $m)) {
            return [(int) $m[3], (int) $m[2], (int) $m[1]];
        }

        return [null, null, null];
    }
}
