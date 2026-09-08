<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\Contract;
use App\Models\Direction;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\TestSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * "Dasturni testlash" uchun soxta ma'lumotlar — 2-bosqich: qabul oqimi
 * (abituriyent -> test -> shartnoma -> to'lov -> talaba -> guruh) va
 * to'g'ridan-to'g'ri import qilingan (abituriyent bosqichisiz) yuqori
 * kurs talabalari — real universitet talaba tarkibini aks ettirish uchun.
 *
 * MUHIM: AcademicStructureSeeder (o'quv yillari, fanlar) va
 * FacultyDirectionSeeder (fakultet/yo'nalishlar) BU SEEDERDAN OLDIN
 * ishga tushirilgan bo'lishi shart — DatabaseSeeder shu tartibni saqlaydi.
 */
class AdmissionSeeder extends Seeder
{
    public function run(): void
    {
        if (Applicant::count() >= 20) {
            $this->command->warn('AdmissionSeeder: Abituriyentlar allaqachon mavjud, o\'tkazib yuborildi.');

            return;
        }

        $years = AcademicYear::orderBy('start_date')->get();
        $directions = Direction::all();

        if ($directions->isEmpty()) {
            $this->command->warn('AdmissionSeeder: Yo\'nalishlar topilmadi — avval FacultyDirectionSeeder ishga tushirilishi kerak.');

            return;
        }

        $this->seedApplicantsPipeline($years);
        $this->seedDirectAdmissionStudents($years, $directions);
        $this->seedStudentGroupsAndAttach($years, $directions);

        $this->command->info('✓ Abituriyentlar, testlar, shartnomalar, to\'lovlar, talabalar va guruhlar yaratildi!');
    }

    /**
     * Abituriyentlarni real oqim bo'yicha (status taqsimoti) yaratadi va
     * har bir statusga mos keladigan keyingi bosqichlarni (test, shartnoma,
     * to'lov, talabaga aylanish) qo'shadi.
     */
    private function seedApplicantsPipeline(Collection $years): void
    {
        $statusWeights = [
            'new'        => 18,
            'accepted'   => 9,
            'interview'  => 9,
            'tested'     => 14,
            'contracted' => 14,
            'enrolled'   => 26,
            'rejected'   => 10,
        ];

        $activeYear = $years->firstWhere('is_active', true) ?? $years->last();

        foreach ($statusWeights as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $applicant = Applicant::factory()->withStatus($status)->create();

                if (in_array($status, ['tested', 'contracted', 'enrolled'], true)) {
                    TestSession::factory()->completed()->create([
                        'applicant_id' => $applicant->id,
                        'direction_id' => $applicant->direction_id,
                    ]);
                }

                $contract = null;
                if (in_array($status, ['contracted', 'enrolled'], true)) {
                    $direction = Direction::find($applicant->direction_id);
                    $contract = $this->createContractWithPayments($direction, ['applicant_id' => $applicant->id]);
                }

                if ($status === 'enrolled' && $activeYear) {
                    $student = Student::factory()->create([
                        'applicant_id'     => $applicant->id,
                        'academic_year_id' => $activeYear->id,
                        'direction_id'     => $applicant->direction_id,
                        'first_name'       => $applicant->first_name,
                        'last_name'        => $applicant->last_name,
                        'middle_name'      => $applicant->middle_name,
                        'gender'           => $applicant->gender,
                        'passport_series'  => $applicant->passport_series,
                        'jshshir'          => $applicant->jshshir,
                        'phone'            => $applicant->phone,
                        'email'            => $applicant->email,
                        'birth_day'        => $applicant->birth_day,
                        'birth_month'      => $applicant->birth_month,
                        'birth_year'       => $applicant->birth_year,
                        'degree'           => $applicant->education_type === 'master' ? 'master' : 'bachelor',
                        'study_form'       => $applicant->study_form,
                        'course_year'      => $applicant->education_type === 'transfer' ? fake()->numberBetween(2, 3) : 1,
                        'status'           => 'active',
                        'funding_type'     => $contract?->payment_type ?? 'grant',
                    ]);

                    $this->attachStudentAccount($student);
                }
            }
        }
    }

    /**
     * Bekor qilinmagan shartnoma yaratadi va unga to'lov(lar) biriktiradi
     * — Contract::refreshStatusFromPayments() orqali holati haqiqiy
     * to'langan summaga mos keladi (xuddi ilovaning o'zidagidek).
     * ~20% grant (bepul) asosida — bunday shartnomada to'lov yo'q.
     */
    private function createContractWithPayments(?Direction $direction, array $owner): Contract
    {
        $isGrant = fake()->boolean(20);
        $amount = $direction && (float) $direction->annual_fee > 0 ? (float) $direction->annual_fee : 16000000;

        if ($isGrant) {
            return Contract::factory()->grant()->create([
                ...$owner,
                'direction_id' => $direction?->id,
                'status'       => 'paid',
            ]);
        }

        $contract = Contract::factory()->create([
            ...$owner,
            'direction_id' => $direction?->id,
            'amount'       => $amount,
            'status'       => 'signed',
        ]);

        $paidFully = fake()->boolean(65);
        $paidAmount = $paidFully ? $amount : round($amount * fake()->randomFloat(2, 0.2, 0.7), 2);

        Payment::factory()->forContract($contract->id)->create(['amount' => $paidAmount]);

        $contract->refreshStatusFromPayments();

        return $contract->fresh();
    }

    /**
     * Abituriyentlar oqimidan o'tmagan (masalan HEMIS'dan import qilingan
     * yoki oldingi yillardan qolgan) 2-3-4-kurs talabalari — aks holda
     * butun talabalar bazasi faqat 1-kurslardan iborat bo'lib qolar edi,
     * bu esa guruhlar/hisobotlarni real sinash imkonini bermas edi.
     */
    private function seedDirectAdmissionStudents(Collection $years, Collection $directions): void
    {
        $earliestYear = $years->first();

        foreach ([2, 3, 4] as $courseYear) {
            for ($i = 0; $i < 15; $i++) {
                $direction = $directions->random();
                $isContract = fake()->boolean(70);

                $student = Student::factory()->create([
                    'academic_year_id' => $earliestYear?->id,
                    'direction_id'     => $direction->id,
                    'course_year'      => $courseYear,
                    'funding_type'     => $isContract ? 'contract' : 'grant',
                    'status'           => 'active',
                ]);

                if ($isContract) {
                    $this->createContractWithPayments($direction, ['student_id' => $student->id]);
                }

                $this->attachStudentAccount($student);
            }
        }
    }

    /**
     * Talaba uchun kirish hisobini (User + 'student' roli) yaratadi va
     * Student.user_id orqali bog'laydi — talaba shaxsiy kabinetiga kira
     * olishi uchun shart.
     */
    private function attachStudentAccount(Student $student): User
    {
        $user = User::factory()->create([
            'full_name' => "{$student->last_name} {$student->first_name} {$student->middle_name}",
            'email'     => $student->email,
            'phone'     => $student->phone,
            'gender'    => $student->gender,
        ]);

        $user->assignRole('student');
        $student->update(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Har bir yo'nalish + kurs uchun 1 ta guruh, va shu yo'nalish/kursdagi
     * BARCHA talabalarni o'sha guruhga biriktiradi (group_students).
     */
    private function seedStudentGroupsAndAttach(Collection $years, Collection $directions): void
    {
        $earliestYear = $years->first();

        foreach ($directions as $direction) {
            foreach ([1, 2, 3, 4] as $courseYear) {
                $studentIds = Student::where('direction_id', $direction->id)
                    ->where('course_year', $courseYear)
                    ->pluck('id');

                if ($studentIds->isEmpty()) {
                    continue;
                }

                $group = StudentGroup::factory()->create([
                    'academic_year_id' => $earliestYear?->id,
                    'direction_id'     => $direction->id,
                    'course_year'      => $courseYear,
                ]);

                $group->students()->attach($studentIds);
            }
        }
    }
}
