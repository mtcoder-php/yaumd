<?php

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Support\UzbekName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * MUHIM TUZATISH: standart Laravel shabloni 'name' ustunini ishlatardi,
 * lekin bu ilovaning 'users' jadvalida 'name' ustuni umuman yo'q (faqat
 * 'full_name') — shu sababli User::factory()->create() chaqirilganda
 * 'full_name' NOT NULL ustuniga hech narsa yozilmay, SQL xatolik berardi.
 * Bu yerda ilovaning haqiqiy 'users' sxemasiga (uuid, full_name,
 * passport_series, jshshir, phone, gender, birth_date...) mos qilib
 * to'liq qayta yozildi.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $person = UzbekName::person();
        $fullName = "{$person['last_name']} {$person['first_name']} {$person['middle_name']}";

        return [
            'full_name'         => $fullName,
            'passport_series'   => null,
            'jshshir'           => null,
            'phone'             => '99890'.fake()->unique()->numerify('#######'),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'birth_date'        => fake()->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
            'gender'            => $person['gender'],
            'address'           => null,
            'photo'             => null,
            'is_active'         => true,
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Foydalanuvchi faol emas (ishdan bo'shatilgan/bloklangan) holatda.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
