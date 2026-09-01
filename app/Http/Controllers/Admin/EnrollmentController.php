<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kursga yozilganlarni (Enrollment) boshqarish — kurs ichida ("O'quv
 * dasturi" bilan bir qatorda) ishlaydi. Bitta talabani yoki butun bir
 * guruhni birdaniga kursga yozish mumkin. Yozish uchun talabaning tizimga
 * kirish hisobi (Student->user_id, StudentAccountService orqali avtomatik
 * yaratiladi) shart — bo'lmasa aniq sabab bilan o'tkazib yuboriladi.
 */
class EnrollmentController extends Controller
{
    public function index(int $courseId): Response
    {
        $course = Course::with('groups')->withCount('enrollments')->findOrFail($courseId);

        $enrollments = Enrollment::where('course_id', $courseId)
            ->with('user')
            ->latest('enrolled_at')
            ->paginate(20);

        return Inertia::render('Admin/Courses/Enrollments', [
            'course'      => $course,
            'enrollments' => $enrollments,
            // Faqat tizimga kirish hisobi bor talabalarni tanlash mumkin
            // (aks holda yozib bo'lmaydi) — shu ro'yxatdan tanlanadi.
            'students'    => Student::whereNotNull('user_id')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'student_number', 'user_id']),
        ]);
    }

    public function store(StoreEnrollmentRequest $request, int $courseId)
    {
        $course = Course::findOrFail($courseId);
        $created = 0;
        $notes = [];

        if ($request->filled('group_id')) {
            $group = StudentGroup::with('students')->findOrFail($request->group_id);

            foreach ($group->students as $student) {
                if ($this->enrollStudent($course, $student, $notes)) {
                    $created++;
                }
            }
        } else {
            $student = Student::findOrFail($request->student_id);

            if ($this->enrollStudent($course, $student, $notes)) {
                $created++;
            }
        }

        $message = $created > 0
            ? "{$created} ta talaba kursga yozildi!"
            : "Hech kim yozilmadi.";

        if ($notes) {
            $message .= ' ' . implode(' ', array_unique($notes));
        }

        return back()->with($created > 0 ? 'success' : 'error', $message);
    }

    public function updatePaymentStatus(Request $request, int $courseId, int $enrollmentId)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $enrollment = Enrollment::where('course_id', $courseId)->findOrFail($enrollmentId);
        $enrollment->update(['payment_status' => $request->payment_status]);

        return back()->with('success', "To'lov holati yangilandi!");
    }

    public function destroy(int $courseId, int $enrollmentId)
    {
        Enrollment::where('course_id', $courseId)->findOrFail($enrollmentId)->delete();

        return back()->with('success', "Talaba kursdan chiqarildi!");
    }

    /**
     * @param  array<int, string>  $notes
     */
    private function enrollStudent(Course $course, Student $student, array &$notes): bool
    {
        if (! $student->user_id) {
            $notes[] = "{$student->last_name} {$student->first_name}: tizimga kirish hisobi yo'q (email/passport to'ldirilmagan), yozilmadi.";
            return false;
        }

        if (Enrollment::where('course_id', $course->id)->where('user_id', $student->user_id)->exists()) {
            // Allaqachon yozilgan — indamay o'tkazib yuboramiz.
            return false;
        }

        $isPaid = (float) $course->price > 0;

        Enrollment::create([
            'course_id'      => $course->id,
            'user_id'        => $student->user_id,
            'payment_type'   => $isPaid ? 'cash' : 'free',
            'payment_status' => $isPaid ? 'pending' : 'paid',
            'status'         => 'active',
            'enrolled_at'    => now(),
        ]);

        return true;
    }
}
