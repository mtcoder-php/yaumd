<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Direction;

class FacultyDirectionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name_uz' => "Mumtoz sharq filologiyasi fakulteti",
                'name_ru' => "Факультет классической восточной филологии",
                'name_en' => "Faculty of Classical Eastern Philology",
                'short_name' => "Sharq filologiyasi",
                'directions' => [
                    ['name_uz' => "Arab tili filologiyasi",             'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 25, 'annual_fee' => 18000000],
                    ['name_uz' => "Sharq mumtoz tili (Arab tili)",      'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 20, 'annual_fee' => 18000000],
                    ['name_uz' => "Lingvistika: Arab tili",             'degree' => 'master',   'duration_years' => 2, 'quota_grant' => 0,  'quota_contract' => 15, 'annual_fee' => 22000000],
                ],
            ],
            [
                'name_uz' => "Tillar fakulteti",
                'name_ru' => "Факультет языков",
                'name_en' => "Faculty of Languages",
                'short_name' => "Tillar",
                'directions' => [
                    ['name_uz' => "Ingliz tili va adabiyoti",           'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 10, 'quota_contract' => 40, 'annual_fee' => 20000000],
                    ['name_uz' => "Rus tili va adabiyoti",              'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 25, 'annual_fee' => 18000000],
                    ['name_uz' => "Xitoy tili va adabiyoti",            'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 30, 'annual_fee' => 22000000],
                    ['name_uz' => "Turk tili va adabiyoti",             'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 25, 'annual_fee' => 20000000],
                    ['name_uz' => "Koreys tili va adabiyoti",           'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 25, 'annual_fee' => 22000000],
                    ['name_uz' => "Lingvistika: Ingliz tili",           'degree' => 'master',   'duration_years' => 2, 'quota_grant' => 0,  'quota_contract' => 15, 'annual_fee' => 22000000],
                ],
            ],
            [
                'name_uz' => "Maxsus pedagogika fakulteti",
                'name_ru' => "Факультет специальной педагогики",
                'name_en' => "Faculty of Special Pedagogy",
                'short_name' => "Maxsus pedagogika",
                'directions' => [
                    ['name_uz' => "Logopediya",                         'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 20, 'annual_fee' => 16000000],
                    ['name_uz' => "Psixologiya",                        'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 30, 'annual_fee' => 16000000],
                ],
            ],
            [
                'name_uz' => "Maktab va maktabgacha ta'lim fakulteti",
                'name_ru' => "Факультет школьного и дошкольного образования",
                'name_en' => "Faculty of School and Preschool Education",
                'short_name' => "Maktab ta'limi",
                'directions' => [
                    ['name_uz' => "Ta'lim va tarbiya nazariyasi va metodikasi", 'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5, 'quota_contract' => 25, 'annual_fee' => 15000000],
                    ['name_uz' => "Maktabgacha ta'lim",                 'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 25, 'annual_fee' => 15000000],
                ],
            ],
            [
                'name_uz' => "Umumta'lim fanlari fakulteti",
                'name_ru' => "Факультет общеобразовательных дисциплин",
                'name_en' => "Faculty of General Education",
                'short_name' => "Umumta'lim",
                'directions' => [
                    ['name_uz' => "Pedagogika",                         'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 25, 'annual_fee' => 15000000],
                    ['name_uz' => "Dizayn: Interyerni loyihalash",      'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 20, 'annual_fee' => 20000000],
                    ['name_uz' => "Dizayn: Liboslar dizayni",           'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 0,  'quota_contract' => 20, 'annual_fee' => 20000000],
                    ['name_uz' => "Iqtisodiyot",                        'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 30, 'annual_fee' => 18000000],
                    ['name_uz' => "Buxgalteriya hisobi va audit",       'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 25, 'annual_fee' => 18000000],
                    ['name_uz' => "Dasturiy injiniring",                'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 35, 'annual_fee' => 24000000],
                    ['name_uz' => "Matematika",                         'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 20, 'annual_fee' => 16000000],
                    ['name_uz' => "Tarix",                              'degree' => 'bachelor', 'duration_years' => 4, 'quota_grant' => 5,  'quota_contract' => 20, 'annual_fee' => 15000000],
                ],
            ],
        ];

        foreach ($data as $facultyData) {
            $faculty = Faculty::create([
                'name_uz'    => $facultyData['name_uz'],
                'name_ru'    => $facultyData['name_ru'],
                'name_en'    => $facultyData['name_en'],
                'short_name' => $facultyData['short_name'],
                'is_active'  => true,
            ]);

            foreach ($facultyData['directions'] as $dir) {
                Direction::create([
                    'faculty_id'      => $faculty->id,
                    'name_uz'         => $dir['name_uz'],
                    'is_active'       => true,
                    'degree'          => $dir['degree'],
                    'duration_years'  => $dir['duration_years'],
                    'quota_grant'     => $dir['quota_grant'],
                    'quota_contract'  => $dir['quota_contract'],
                    'annual_fee'      => $dir['annual_fee'],
                ]);
            }
        }

        $this->command->info('✓ ' . count($data) . ' ta fakultet va yo\'nalishlar yaratildi!');
    }
}
