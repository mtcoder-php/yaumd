<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * CRM — Qarzdorlar ro'yxatini Excel'ga eksport qilish.
 *
 * CrmDebtorController'da tayyorlangan (allaqachon qarzi bor deb filtrlangan
 * va saralangan) qatorlar massivini qabul qiladi — hisoblash mantig'i shu
 * yerda emas, kontrollerda (bitta joyda) turadi, shunda index() sahifasi
 * va eksport har doim bir xil natijani ko'rsatadi.
 */
class CrmDebtorExportService
{
    private const HEADERS = [
        'Shartnoma raqami', 'F.I.Sh', 'Telefon', "Yo'nalish", 'Fakultet',
        'Jami summa', "To'langan", 'Qolgan qarz', 'Holati', 'Imzolangan sana',
    ];

    /**
     * @param  Collection<int, array>  $debtors
     */
    public function export(Collection $debtors): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Qarzdorlar');

        foreach (self::HEADERS as $col => $title) {
            $sheet->setCellValue([$col + 1, 1], $title);
        }
        $sheet->getStyle([1, 1, count(self::HEADERS), 1])->getFont()->setBold(true);

        $row = 2;
        foreach ($debtors as $debtor) {
            $values = [
                $debtor['contract_number'],
                $debtor['person']['full_name'] ?? '—',
                $debtor['person']['phone'] ?? '—',
                $debtor['direction']['name_uz'] ?? '—',
                $debtor['direction']['faculty'] ?? '—',
                $debtor['amount'],
                $debtor['paid_amount'],
                $debtor['remaining_amount'],
                $this->statusLabel($debtor['status']),
                $debtor['signed_at'] ? date('d.m.Y', strtotime($debtor['signed_at'])) : '—',
            ];

            foreach ($values as $col => $value) {
                $sheet->setCellValue([$col + 1, $row], $value);
            }
            $row++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

    private function statusLabel(?string $v): string
    {
        return match ($v) {
            'draft'  => 'Qoralama',
            'signed' => 'Imzolangan',
            'paid'   => "To'langan",
            default  => (string) $v,
        };
    }
}
