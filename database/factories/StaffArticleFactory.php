<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StaffArticle>
 */
class StaffArticleFactory extends Factory
{
    private const TITLES = [
        'Raqamli ta\'lim tizimlarida ma\'lumotlar xavfsizligi masalalari',
        'Talabalar bilim darajasini baholashning intellektual usullari',
        'Oliy ta\'lim muassasalarida axborot tizimlarini integratsiyalash',
        'Masofaviy ta\'limda sun\'iy intellekt texnologiyalaridan foydalanish',
        'Katta hajmdagi ma\'lumotlarni tahlil qilishning zamonaviy yondashuvlari',
        'Kiberxavfsizlik: universitet tarmoqlarini himoyalash tajribasi',
        'Elektron hukumat xizmatlarida foydalanuvchi interfeysi dizayni',
        'Ta\'lim jarayonini raqamlashtirishning ijtimoiy-iqtisodiy samarasi',
    ];

    private const SOURCES = [
        'O\'zMU xabarlari ilmiy jurnali', 'TATU Axborotnomasi',
        'Xalqaro ilmiy-amaliy konferensiya materiallari', 'Scopus indeksli jurnal',
        'Ta\'lim texnologiyalari jurnali', 'Respublika ilmiy-texnik anjumani',
    ];

    public function definition(): array
    {
        return [
            'title'      => fake()->randomElement(self::TITLES),
            'source_uz'  => fake()->randomElement(self::SOURCES),
            'year'       => (string) fake()->numberBetween(2019, 2026),
            'url'        => null,
            'sort_order' => 0,
        ];
    }
}
