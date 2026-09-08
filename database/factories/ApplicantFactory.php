<?php

namespace Database\Factories;

use App\Models\Direction;
use App\Models\District;
use App\Models\Region;
use Database\Factories\Support\UzbekName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Applicant>
 */
class ApplicantFactory extends Factory
{
    public function definition(): array
    {
        $person = UzbekName::person();
        $region = Region::inRandomOrder()->first();
        $district = $region ? District::where('region_id', $region->id)->inRandomOrder()->value('id') : null;

        // AdmissionController'dagi bilan bir xil andoza: "<yil>-<tartib raqam>".
        $applicationNumber = now()->year.'-'.fake()->unique()->numerify('####');

        return [
            'user_id'                => null,
            'application_number'     => $applicationNumber,
            'education_type'         => fake()->randomElement(['bachelor', 'bachelor', 'bachelor', 'master', 'transfer', 'second']),
            'study_form'             => fake()->randomElement(['full_time', 'full_time', 'evening', 'distance']),
            'direction_id'           => Direction::inRandomOrder()->value('id'),
            'first_name'             => $person['first_name'],
            'last_name'              => $person['last_name'],
            'middle_name'            => $person['middle_name'],
            'birth_day'              => fake()->numberBetween(1, 28),
            'birth_month'            => fake()->numberBetween(1, 12),
            'birth_year'             => fake()->numberBetween(now()->year - 30, now()->year - 17),
            'gender'                 => $person['gender'],
            'nationality'            => "O'zbek",
            'passport_series'        => Str::upper(fake()->lexify('??')).fake()->numerify('#######'),
            'jshshir'                => fake()->unique()->numerify('##############'),
            'passport_file'          => null,
            'phone'                  => '99890'.fake()->unique()->numerify('#######'),
            'extra_phone'            => null,
            'email'                  => fake()->unique()->safeEmail(),
            'diploma_file'           => null,
            'diploma_appendix_file'  => null,
            'region_id'              => $region?->id,
            'district_id'            => $district,
            'address'                => fake()->streetAddress(),
            'previous_diploma'       => null,
            'previous_edu_place'     => fake()->randomElement([
                '1-maktab', "20-akademik litsey", "Iqtisodiyot kolleji", null,
            ]),
            'status'                 => 'new',
            'interview_at'           => null,
        ];
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
