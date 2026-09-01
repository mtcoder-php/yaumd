<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Notifications\StudentAccountCreatedNotification;
use Illuminate\Support\Facades\Hash;

/**
 * Talabaga (Abituriyentdan aylantirilgan, qo'lda qo'shilgan yoki HEMIS'dan
 * import qilingan — barcha manba uchun bitta joy) tizimga kirish hisobini
 * (User) avtomatik yaratadi:
 *  - login: talabaning email manzili
 *  - parol (default): talabaning passport seriya raqami
 *
 * Idempotent: talaba allaqachon hisobga (user_id) bog'langan bo'lsa hech
 * narsa qilmaydi, shuning uchun bir xil talaba uchun bir necha marta
 * (masalan Edit sahifasida saqlashda yoki HEMIS'ni qayta import qilishda)
 * xavfsiz chaqirilishi mumkin.
 */
class StudentAccountService
{
    /**
     * @return array{created: bool, message: ?string}
     */
    public function provision(Student $student): array
    {
        // Allaqachon hisobi bog'langan bo'lsa — hech narsa qilmaymiz
        if ($student->user_id) {
            return ['created' => false, 'message' => null];
        }

        if (! $student->email) {
            return [
                'created' => false,
                'message' => "{$student->last_name} {$student->first_name}: email manzili kiritilmagani uchun login-parol yaratilmadi.",
            ];
        }

        if (! $student->passport_series) {
            return [
                'created' => false,
                'message' => "{$student->last_name} {$student->first_name}: passport seriya raqami kiritilmagani uchun login-parol yaratilmadi.",
            ];
        }

        // Shu email bilan foydalanuvchi allaqachon mavjud bo'lsa (masalan,
        // boshqa rolda ham ishlaydigan xodim) — yangi hisob yaratmasdan,
        // mavjudini talabaga bog'laymiz va 'student' rolini qo'shamiz.
        $existing = User::where('email', $student->email)->first();

        if ($existing) {
            $student->update(['user_id' => $existing->id]);

            if (! $existing->hasRole('student')) {
                $existing->assignRole('student');
            }

            return [
                'created' => false,
                'message' => "{$student->last_name} {$student->first_name}: shu email bilan mavjud hisobga ({$existing->email}) bog'landi.",
            ];
        }

        $user = User::create([
            'full_name'       => trim("{$student->last_name} {$student->first_name} {$student->middle_name}"),
            'email'           => $student->email,
            'password'        => Hash::make($student->passport_series),
            'passport_series' => $student->passport_series,
            'jshshir'         => $student->jshshir,
            'phone'           => $student->phone,
            'is_active'       => true,
        ]);

        $user->assignRole('student');

        $student->update(['user_id' => $user->id]);

        $user->notify(new StudentAccountCreatedNotification($student->passport_series));

        return ['created' => true, 'message' => null];
    }
}
