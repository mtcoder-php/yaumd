<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * CRM — Talabalar ro'yxatini Excel'ga eksport qilish.
 *
 * StudentController@index'dagi FILTRLANGAN so'rovni (qidiruv, yo'nalish,
 * o'quv yili va h.k.) qabul qiladi — shu bilan "sahifadagi ko'rinishni
 * eksport qilish" tamoyili ta'minlanadi (nafaqat butun jadvalni).
 */
class StudentExportService
{
    private const HEADERS = [
        'Talaba raqami', 'HEMIS ID', 'F.I.Sh', "Yo'nalish", 'Kafedra',
        "O'quv yili", 'Kurs', 'Daraja', "O'qish shakli",
        'Moliyalashtirish', 'Holati', 'Telefon', 'Email',
    ];

    public function export(Builder $query): string
    {
        $students = $query
            ->with(['academicYear', 'direction', 'department'])
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Talabalar');

        foreach (self::HEADERS as $col => $title) {
            $sheet->setCellValue([$col + 1, 1], $title);
        }
        $sheet->getStyle([1, 1, count(self::HEADERS), 1])->getFont()->setBold(true);

        $row = 2;
        foreach ($students as $student) {
            $values = [
                $student->student_number,
                $student->hemis_id,
                trim("{$student->last_name} {$student->first_name} {$student->middle_name}"),
                $student->direction?->name_uz,
                $student->department?->name_uz,
                $student->academicYear?->name,
                $student->course_year,
                $this->degreeLabel($student->degree),
                $this->studyFormLabel($student->study_form),
                $this->fundingLabel($student->funding_type),
                $this->statusLabel($student->status),
                $student->phone,
                $student->email,
            ];

            foreach ($values as $col => $value) {
                $sheet->setCellValue([$col + 1, $row], $value);
            }
            $row++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

    private function degreeLabel(?string $v): string
    {
        return match ($v) {
            'bachelor' => 'Bakalavr',
            'master'   => 'Magistr',
            default    => (string) $v,
        };
    }

    private function studyFormLabel(?string $v): string
    {
        return match ($v) {
            'full_time' => 'Kunduzgi',
            'evening'   => 'Kechki',
            'distance'  => 'Sirtqi',
            default     => (string) $v,
        };
    }

    private function fundingLabel(?string $v): string
    {
        return match ($v) {
            'grant'    => 'Grant',
            'contract' => 'Kontrakt',
            default    => (string) $v,
        };
    }

    private function statusLabel(?string $v): string
    {
        return match ($v) {
            'active'         => 'Faol',
            'academic_leave' => "Akademik ta'til",
            'expelled'       => 'Chetlashtirilgan',
            'graduated'      => 'Bitirgan',
            'transferred'    => "Ko'chirilgan",
            default          => (string) $v,
        };
    }
}
