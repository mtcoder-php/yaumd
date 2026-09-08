<?php

namespace Database\Factories\Support;

/**
 * Faker'ning standart (en_US) lokalizatsiyasi "John Smith" kabi ismlar
 * generatsiya qiladi — bu universitet talaba/abituriyent/xodim ma'lumotlari
 * uchun real ko'rinmaydi (fakerphp/faker'da uz_UZ lokali yo'q). Shu sababli
 * bir nechta Factory (Applicant, Student, Staff, User) shu yordamchi
 * klassdan FOYDALANADI — real Uzbekcha ism/familiya/sharif generatsiya
 * qilish uchun. Bu Factory emas, oddiy statik yordamchi klass.
 */
class UzbekName
{
    private const MALE_FIRST_NAMES = [
        'Aziz', 'Bekzod', 'Davron', 'Elyor', 'Farrux', 'Gayrat', 'Husan', 'Ibrohim',
        'Jasur', 'Kamron', 'Lochin', 'Muhammadali', 'Nodirbek', 'Otabek', 'Parviz',
        'Qodir', 'Rustam', 'Sardor', 'Temur', 'Ulug\'bek', 'Vohid', 'Xurshid',
        'Yusuf', 'Zafar', 'Bobur', 'Diyorbek', 'Eldor', 'Firdavs', 'G\'ayrat',
        'Hasan', 'Islom', 'Jahongir', 'Komil', 'Mirjalol', 'Nurbek',
    ];

    private const FEMALE_FIRST_NAMES = [
        'Amira', 'Barno', 'Dilnoza', 'Ezoza', 'Farida', 'Gulnora', 'Hilola', 'Iroda',
        'Jasmina', 'Kamila', 'Laylo', 'Madina', 'Nigora', 'Ozoda', 'Parizoda',
        'Robiya', 'Sevinch', 'Gulchehra', 'Umida', 'Vazira', 'Xurshida', 'Yulduz',
        'Zarina', 'Aziza', 'Dildora', 'Feruza', 'Gulbahor', 'Malika', 'Nilufar',
        'Sabina', 'Shahnoza', 'To\'lqin', 'Zebiniso', 'Rayhona',
    ];

    // [erkak familiyasi, ayol familiyasi]
    private const SURNAMES = [
        ['Karimov', 'Karimova'], ['Rashidov', 'Rashidova'], ['Yusupov', 'Yusupova'],
        ['Aliyev', 'Aliyeva'], ['Tursunov', 'Tursunova'], ['Nazarov', 'Nazarova'],
        ['Ergashev', 'Ergasheva'], ['Xolmatov', 'Xolmatova'], ['Sodiqov', 'Sodiqova'],
        ['Rahimov', 'Rahimova'], ['Abdullayev', 'Abdullayeva'], ['Islomov', 'Islomova'],
        ['Yoldashev', 'Yoldasheva'], ['Mirzayev', 'Mirzayeva'], ['Qodirov', 'Qodirova'],
        ['Saidov', 'Saidova'], ['Toshpo\'latov', 'Toshpo\'latova'], ['Ismoilov', 'Ismoilova'],
        ['Xudoyberdiyev', 'Xudoyberdiyeva'], ['Ne\'matov', 'Ne\'matova'],
        ['Ochilov', 'Ochilova'], ['Jo\'rayev', 'Jo\'rayeva'], ['Bekmurodov', 'Bekmurodova'],
        ['Shokirov', 'Shokirova'], ['Umarov', 'Umarova'],
    ];

    /**
     * @return array{first_name: string, last_name: string, middle_name: string, gender: string}
     */
    public static function person(?string $gender = null): array
    {
        $gender ??= fake()->randomElement(['male', 'female']);
        $isMale = $gender === 'male';

        $firstName = fake()->randomElement($isMale ? self::MALE_FIRST_NAMES : self::FEMALE_FIRST_NAMES);
        $surnamePair = fake()->randomElement(self::SURNAMES);
        $lastName = $isMale ? $surnamePair[0] : $surnamePair[1];

        // Otasining ismi (sharif) — "<ism> o'g'li"/"qizi" andozasi.
        $fatherFirstName = fake()->randomElement(self::MALE_FIRST_NAMES);
        $middleName = $fatherFirstName.' '.($isMale ? "o'g'li" : 'qizi');

        return [
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'middle_name' => $middleName,
            'gender'      => $gender,
        ];
    }
}
