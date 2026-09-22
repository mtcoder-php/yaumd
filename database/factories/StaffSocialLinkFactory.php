<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StaffSocialLink>
 */
class StaffSocialLinkFactory extends Factory
{
    /**
     * Har bir platforma uchun (label, url) generatori — frontendda
     * platformaga mos icon tanlash uchun 'platform' kaliti ishlatiladi.
     */
    private const PLATFORMS = ['telegram', 'linkedin', 'google_scholar', 'researchgate'];

    public function definition(): array
    {
        return $this->attributesFor(fake()->randomElement(self::PLATFORMS));
    }

    /**
     * MUHIM: platformani oddiy ->create(['platform' => ...]) orqali
     * berish yetarli emas edi — definition() ichida allaqachon tasodifiy
     * platforma uchun mos label/url tanlanib bo'lgan bo'lardi va keyin
     * faqat 'platform' ustuncha almashtirilib, url/label mos kelmay
     * qolardi. Shuning uchun aniq platforma uchun label/url bilan birga
     * to'liq holat qaytaruvchi alohida state method.
     */
    public function platform(string $platform): static
    {
        return $this->state(fn () => $this->attributesFor($platform));
    }

    private function attributesFor(string $platform): array
    {
        $slug = fake()->userName();

        return match ($platform) {
            'telegram'       => ['platform' => 'telegram', 'label' => "@{$slug}", 'url' => "https://t.me/{$slug}"],
            'linkedin'       => ['platform' => 'linkedin', 'label' => fake()->name(), 'url' => "https://linkedin.com/in/{$slug}"],
            'google_scholar' => ['platform' => 'google_scholar', 'label' => 'Google Scholar', 'url' => "https://scholar.google.com/citations?user={$slug}"],
            default          => ['platform' => 'researchgate', 'label' => 'ResearchGate', 'url' => "https://researchgate.net/profile/{$slug}"],
        };
    }
}
