<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultyDirectionSeeder extends Seeder
{
    public function run(): void
    {
        // Fakultet
        $facultyId = DB::table('faculties')->insertGetId([
            'name_uz'    => 'Yangi Asr Universiteti',
            'name_ru'    => 'Университет Нового Века',
            'name_en'    => 'New Age University',
            'short_name' => 'YAU',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kafedralar
        $departments = [
            [
                'name_uz'    => 'Maxsus pedagogika kafedrasi',
                'name_ru'    => 'Кафедра специальной педагогики',
                'short_name' => 'MPK',
            ],
            [
                'name_uz'    => "Umumta'lim fanlari kafedrasi",
                'name_ru'    => 'Кафедра общеобразовательных дисциплин',
                'short_name' => 'UFK',
            ],
            [
                'name_uz'    => 'Tillar kafedrasi',
                'name_ru'    => 'Кафедра языков',
                'short_name' => 'TK',
            ],
            [
                'name_uz'    => 'Maktab va maktabgacha ta\'lim kafedrasi',
                'name_ru'    => 'Кафедра школьного и дошкольного образования',
                'short_name' => 'MMTK',
            ],
            [
                'name_uz'    => 'Mumtoz sharq filologiyasi kafedrasi',
                'name_ru'    => 'Кафедра классической восточной филологии',
                'short_name' => 'MSFK',
            ],
            [
                'name_uz'    => 'Sharq filologiyasi kafedrasi',
                'name_ru'    => 'Кафедра восточной филологии',
                'short_name' => 'SFK',
            ],
        ];

        $departmentIds = [];
        foreach ($departments as $dept) {
            $departmentIds[$dept['short_name']] = DB::table('departments')->insertGetId([
                'faculty_id' => $facultyId,
                'name_uz'    => $dept['name_uz'],
                'name_ru'    => $dept['name_ru'],
                'short_name' => $dept['short_name'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Yo'nalishlar — qaysi kafedrada ekanini belgilang
        $directions = [
            // Maxsus pedagogika kafedrasi
            [
                'department' => 'MPK',
                'name_uz'    => 'Maxsus pedagogika',
                'name_ru'    => 'Специальная педагогика',
                'hemis_code' => '5110900',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 10,
                'quota_contract' => 30,
                'annual_fee'     => 16000000,
            ],
            // Umumta'lim fanlari kafedrasi
            [
                'department' => 'UFK',
                'name_uz'    => 'Matematika',
                'name_ru'    => 'Математика',
                'hemis_code' => '5140200',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 10,
                'quota_contract' => 30,
                'annual_fee'     => 16000000,
            ],
            // Tillar kafedrasi
            [
                'department' => 'TK',
                'name_uz'    => 'Ingliz tili va adabiyoti',
                'name_ru'    => 'Английский язык и литература',
                'hemis_code' => '5120200',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 15,
                'quota_contract' => 40,
                'annual_fee'     => 18000000,
            ],
            [
                'department' => 'TK',
                'name_uz'    => 'Arab tili va adabiyoti',
                'name_ru'    => 'Арабский язык и литература',
                'hemis_code' => '5120600',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 10,
                'quota_contract' => 30,
                'annual_fee'     => 18000000,
            ],
            // Maktab va maktabgacha ta'lim kafedrasi
            [
                'department' => 'MMTK',
                'name_uz'    => "Boshlang'ich ta'lim",
                'name_ru'    => 'Начальное образование',
                'hemis_code' => '5111000',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 10,
                'quota_contract' => 30,
                'annual_fee'     => 15000000,
            ],
            [
                'department' => 'MMTK',
                'name_uz'    => "Maktabgacha ta'lim",
                'name_ru'    => 'Дошкольное образование',
                'hemis_code' => '5111100',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 10,
                'quota_contract' => 30,
                'annual_fee'     => 15000000,
            ],
            // Mumtoz sharq filologiyasi kafedrasi
            [
                'department' => 'MSFK',
                'name_uz'    => 'Mumtoz sharq filologiyasi',
                'name_ru'    => 'Классическая восточная филология',
                'hemis_code' => '5120700',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 5,
                'quota_contract' => 20,
                'annual_fee'     => 16000000,
            ],
            // Sharq filologiyasi kafedrasi
            [
                'department' => 'SFK',
                'name_uz'    => 'Sharq filologiyasi',
                'name_ru'    => 'Восточная филология',
                'hemis_code' => '5120800',
                'degree'     => 'bachelor',
                'duration_years' => 4,
                'quota_grant'    => 5,
                'quota_contract' => 20,
                'annual_fee'     => 16000000,
            ],
        ];

        foreach ($directions as $direction) {
            $deptKey = $direction['department'];
            unset($direction['department']);

            DB::table('directions')->insert([
                ...$direction,
                'faculty_id'    => $facultyId,
                'department_id' => $departmentIds[$deptKey],
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->command->info('✓ Fakultet, kafedralar va yo\'nalishlar yaratildi!');
    }
}
