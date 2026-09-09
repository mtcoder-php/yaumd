<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    public function definition(): array
    {
        $direction = Direction::inRandomOrder()->first();
        $baseAmount = $direction?->annual_fee > 0 ? $direction->annual_fee : fake()->randomElement([15000000, 16000000, 18000000, 20000000]);

        return [
            'applicant_id'    => null,
            'student_id'      => null,
            'direction_id'    => $direction?->id,
            'contract_number' => Contract::generateNumber(),
            // Yo'nalishning yillik kontrakt narxiga mos — real ma'lumot
            // yo'q bo'lsa, o'rtacha diapazondan tasodifiy qiymat.
            'amount'          => $baseAmount,
            'base_amount'     => $baseAmount,
            'discount_percent' => 0,
            'discount_reason'  => null,
            'discount_note'    => null,
            'payment_type'    => 'contract',
            'status'          => 'signed',
            'pdf_path'        => null,
            'qr_code'         => null,
            'otp_code'        => null,
            'otp_expires_at'  => null,
            'signed_at'       => now()->subDays(fake()->numberBetween(1, 300)),
        ];
    }

    /**
     * Test/demo maqsadida — chegirma qo'llangan kontrakt holati. Berilgan
     * foizga mos ravishda 'amount' ('base_amount'dan) qayta hisoblanadi,
     * xuddi ContractController'dagi kabi.
     */
    public function withDiscount(int $percent, string $reason = 'family', ?string $note = null): static
    {
        return $this->state(function (array $attributes) use ($percent, $reason, $note) {
            $base = $attributes['base_amount'] ?? $attributes['amount'] ?? 0;

            return [
                'base_amount'      => $base,
                'discount_percent' => $percent,
                'discount_reason'  => $reason,
                'discount_note'    => $reason === 'other' ? ($note ?? "Boshqa sabab") : null,
                'amount'           => round($base * (1 - $percent / 100), 2),
            ];
        });
    }

    public function forApplicant(int $applicantId): static
    {
        return $this->state(fn () => ['applicant_id' => $applicantId, 'student_id' => null]);
    }

    public function forStudent(int $studentId): static
    {
        return $this->state(fn () => ['student_id' => $studentId, 'applicant_id' => null]);
    }

    public function grant(): static
    {
        return $this->state(fn () => ['payment_type' => 'grant', 'amount' => 0, 'base_amount' => 0]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'signed_at' => null]);
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid']);
    }
}
