<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

/**
 * Xodimlar (o'qituvchi, tutor, kutubxonachi, moliya va h.k.) ro'yxatini
 * Excel orqali ommaviy import qilish — 'HemisImportService' (talabalar
 * uchun) bilan AYNAN BIR XIL naqsh: readRows() (PhpSpreadsheet bilan xom
 * o'qish) va importRows() (sof PHP massiv bilan biznes mantiq, shu sababli
 * alohida test qilinishi mumkin) ikkiga bo'lingan.
 *
 * KIRISH HISOBI KONVENSIYASI — talabalarnikiga o'xshash
 * (StudentAccountService'ga qarang): login — email, standart parol —
 * passport seriya raqami. Agar qatorda passport bo'lmasa, tasodifiy parol
 * generatsiya qilinadi va natija ro'yxatida (errors/eslatmalar) ko'rsatiladi
 * — buni albatta xodimga ayting, chunki boshqa hech qayerda saqlanmaydi.
 */
class UserImportService
{
    private const HEADER_ALIASES = [
        'full_name' => ['toliq ism', 'ism familiya', 'ism-familiya', 'fio', 'full name'],
        'email' => ['email', 'elektron pochta'],
        'phone' => ['telefon', 'tel'],
        'passport_series' => ['passport', 'passport seriya', 'passport seriya va raqami'],
        'jshshir' => ['jshshir', 'jshir'],
        'birth_date' => ['tugilgan sana', 'tugilgan kun'],
        'gender' => ['jinsi', 'jins'],
        'address' => ['manzil', 'yashash manzili'],
        'roles' => ['rol', 'rollar', 'lavozim', 'role', 'roles'],
    ];

    private const GENDER_MAP = [
        'erkak' => 'male', 'male' => 'male', 'm' => 'male',
        'ayol' => 'female', 'female' => 'female', 'f' => 'female', 'a' => 'female',
    ];

    /**
     * Excel'da yozilishi mumkin bo'lgan rol nomlari — ham ichki (inglizcha)
     * nom, ham RolePermissionSeeder'dagi o'zbekcha ko'rsatilish nomi orqali
     * (normallashtirilgan holda, ya'ni kichik harf + tutuqsiz).
     */
    private const ROLE_ALIASES = [
        'super-admin' => 'super-admin', 'super admin' => 'super-admin',
        'admin' => 'admin',
        'admission' => 'admission', 'qabul xodimi' => 'admission', 'qabul' => 'admission',
        'teacher' => 'teacher', 'oqituvchi' => 'teacher',
        'tutor' => 'tutor',
        'librarian' => 'librarian', 'kutubxonachi' => 'librarian',
        'finance' => 'finance', 'moliya xodimi' => 'finance', 'moliya' => 'finance',
        'student' => 'student', 'talaba' => 'student',
    ];

    public function template(): string
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Xodimlar');

        $headers = [
            "To'liq ism", 'Email', 'Telefon', 'Passport seriya va raqami',
            'JSHSHIR', "Tug'ilgan sana", 'Jinsi', 'Manzil', 'Rol(lar)',
        ];
        foreach ($headers as $col => $title) {
            $sheet->setCellValue([$col + 1, 1], $title);
        }

        $example = [
            'Aliyev Vali Valiyevich', 'vali@example.com', '+998901234567',
            'AB1234567', '30101950000000', '15.03.1985', 'Erkak',
            'Toshkent sh.', "o'qituvchi",
        ];
        foreach ($example as $col => $value) {
            $sheet->setCellValue([$col + 1, 2], $value);
        }

        $help = $spreadsheet->createSheet();
        $help->setTitle("Yo'riqnoma");
        $help->setCellValue('A1', 'Qabul qilinadigan qiymatlar:');
        $help->setCellValue('A2', 'Jinsi: Erkak / Ayol');
        $help->setCellValue('A3', "Rol(lar) — vergul bilan bir nechtasi: admin, qabul xodimi, o'qituvchi, tutor, kutubxonachi, moliya xodimi (kamida bittasi MAJBURIY).");
        $help->setCellValue('A4', "To'liq ism va Email MAJBURIY. Email allaqachon mavjud bo'lsa — o'sha xodimning ma'lumotlari yangilanadi (parol o'zgarmaydi).");
        $help->setCellValue('A5', "Standart parol — Passport seriya va raqami (talabalar uchun ishlatilgan konvensiya bilan bir xil). Bo'sh qoldirilsa, tasodifiy parol yaratiladi va natija ro'yxatida ko'rsatiladi.");

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function readRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, false);
    }

    public function import(UploadedFile $file): array
    {
        return $this->importRows($this->readRows($file));
    }

    /**
     * @param array<int, array<int, mixed>> $rows Birinchi qator sarlavhalar
     * @return array{created:int, updated:int, skipped:int, errors:array<int,string>}
     */
    public function importRows(array $rows): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        if (empty($rows)) {
            return $result;
        }

        $headerRow = array_shift($rows);
        $columnMap = $this->mapHeaders($headerRow);

        $availableRoles = Role::pluck('name')->all();

        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2;

            if ($this->isRowEmpty($row)) {
                continue;
            }

            $data = $this->extractRowData($row, $columnMap);

            $fullName = trim((string) ($data['full_name'] ?? ''));
            $email = trim((string) ($data['email'] ?? ''));

            if ($fullName === '' || $email === '') {
                $result['errors'][] = "{$rowNumber}-qator: To'liq ism yoki Email bo'sh";
                $result['skipped']++;

                continue;
            }

            $roleNames = $this->mapRoles($data['roles'] ?? null, $availableRoles);

            if (empty($roleNames)) {
                $result['errors'][] = "{$rowNumber}-qator: \"{$fullName}\" uchun kamida bitta TO'G'RI rol topilmadi (Rol(lar) ustunini tekshiring)";
                $result['skipped']++;

                continue;
            }

            [$birthDay, $birthMonth, $birthYear] = $this->parseBirthDate($data['birth_date'] ?? null);
            $birthDate = $birthYear
                ? sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay)
                : null;

            try {
                DB::transaction(function () use ($email, $fullName, $data, $roleNames, $birthDate, $rowNumber, &$result) {
                    $user = User::where('email', $email)->first();

                    $attributes = [
                        'full_name' => $fullName,
                        'phone' => $data['phone'] ?: null,
                        'passport_series' => $data['passport_series'] ?: null,
                        'jshshir' => $data['jshshir'] ?: null,
                        'birth_date' => $birthDate,
                        'gender' => $this->mapGender($data['gender'] ?? null),
                        'address' => $data['address'] ?: null,
                    ];

                    if ($user) {
                        // MUHIM: parol bu yerda HECH QACHON o'zgartirilmaydi —
                        // xodim allaqachon o'z parolini bilishi/o'zgartirgan
                        // bo'lishi mumkin, qayta import uni kutilmaganda
                        // almashtirib qo'ymasligi kerak.
                        $user->update($attributes);
                        $result['updated']++;
                    } else {
                        $generatedPassword = null;
                        $password = $data['passport_series'] ?: null;

                        if (! $password) {
                            $generatedPassword = Str::upper(Str::random(8));
                            $password = $generatedPassword;
                        }

                        $user = User::create([
                            ...$attributes,
                            'email' => $email,
                            'password' => Hash::make($password),
                        ]);
                        $result['created']++;

                        if ($generatedPassword) {
                            $result['errors'][] = "{$rowNumber}-qator: \"{$fullName}\" uchun avtomatik parol yaratildi: {$generatedPassword} — buni albatta xodimning o'ziga ayting (boshqa hech qayerda ko'rsatilmaydi).";
                        }
                    }

                    $user->syncRoles($roleNames);
                });
            } catch (\Throwable $e) {
                $result['errors'][] = "{$rowNumber}-qator: bazaga yozishda xatolik ({$e->getMessage()})";
                $result['skipped']++;
            }
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $rawHeader) {
            $normalized = $this->normalizeText((string) $rawHeader);

            foreach (self::HEADER_ALIASES as $key => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$index] = $key;

                    break;
                }
            }
        }

        return $map;
    }

    private function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(["'", '’', '‘', 'ʻ', 'ʼ', '(', ')'], '', $value);
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

        return self::GENDER_MAP[$this->normalizeText($value)] ?? null;
    }

    /**
     * "o'qituvchi, tutor" kabi vergul (yoki nuqtali vergul) bilan
     * ajratilgan matnni haqiqiy, bazada MAVJUD rol nomlariga aylantiradi —
     * noma'lum/yozuvda xato bo'lgan rollar jimgina o'tkazib yuboriladi
     * (butun qator rad etilmaydi, agar KAMIDA bitta to'g'ri rol topilsa).
     *
     * @param array<int, string> $availableRoles
     * @return array<int, string>
     */
    private function mapRoles(mixed $value, array $availableRoles): array
    {
        if (! $value) {
            return [];
        }

        $parts = preg_split('/[,;\/]+/u', (string) $value) ?: [];
        $resolved = [];

        foreach ($parts as $part) {
            $normalized = $this->normalizeText($part);

            if ($normalized === '') {
                continue;
            }

            $roleName = self::ROLE_ALIASES[$normalized] ?? (in_array($normalized, $availableRoles, true) ? $normalized : null);

            if ($roleName && in_array($roleName, $availableRoles, true)) {
                $resolved[$roleName] = true;
            }
        }

        return array_keys($resolved);
    }

    /**
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
