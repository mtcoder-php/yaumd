<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    // Universitet qabul testlarida haqiqatda uchraydigan fanlar — Faker'ning
    // tasodifiy so'zlaridan ko'ra ancha real ko'rinishi uchun. `public` —
    // AcademicStructureSeeder shu ro'yxatning BARCHASINI (tasodifiy emas)
    // bir marta yaratish uchun to'g'ridan-to'g'ri o'qiydi.
    public const NAMES = [
        ['uz' => 'Ona tili va adabiyot',        'ru' => 'Родной язык и литература'],
        ['uz' => 'Tarix',                        'ru' => 'История'],
        ['uz' => 'Matematika',                   'ru' => 'Математика'],
        ['uz' => 'Ingliz tili',                  'ru' => 'Английский язык'],
        ['uz' => 'Arab tili',                    'ru' => 'Арабский язык'],
        ['uz' => 'Pedagogika-psixologiya',       'ru' => 'Педагогика-психология'],
        ['uz' => 'Maxsus pedagogika asoslari',   'ru' => 'Основы специальной педагогики'],
        ['uz' => 'Fizika',                       'ru' => 'Физика'],
        ['uz' => 'Ijtimoiy fanlar',               'ru' => 'Обществознание'],
        ['uz' => 'Sharq mumtoz adabiyoti',       'ru' => 'Классическая восточная литература'],
        ['uz' => "O'zbekiston tarixi",           'ru' => 'История Узбекистана'],
        ['uz' => 'Davlat va huquq asoslari',     'ru' => 'Основы государства и права'],
    ];

    public function definition(): array
    {
        $pair = fake()->randomElement(self::NAMES);

        return [
            'name_uz'   => $pair['uz'],
            'name_ru'   => $pair['ru'],
            'is_active' => true,
        ];
    }
}
