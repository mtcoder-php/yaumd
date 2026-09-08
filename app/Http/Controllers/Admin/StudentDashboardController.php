<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Enrollment;
use App\Models\LibraryAccess;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Talabaning shaxsiy dashboard'i — DashboardController'dagi umumiy
 * (abituriyentlar/shartnomalar statistikasi) sahifadan butunlay farq
 * qiladi: bu yerda faqat TALABANING O'ZIGA tegishli ma'lumot (kurslari,
 * progressi, kutubxonasi, shartnomasi) ko'rsatiladi.
 *
 * DashboardController shu klassni inject qilib, "student" rolidagi (va
 * boshqa hech qanday xodim roli bo'lmagan) foydalanuvchi uchun shu yerga
 * yo'naltiradi — '/admin/dashboard' marshruti o'zgarmaydi.
 */
class StudentDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $enrollments = Enrollment::where('user_id', $userId)->with('course')->get();
        $activeEnrollments = $enrollments->where('status', 'active');

        $continueLearning = $activeEnrollments
            ->sortByDesc('enrolled_at')
            ->take(3)
            ->map(fn (Enrollment $e) => [
                'enrollment_id' => $e->id,
                'course_id'     => $e->course_id,
                'title'         => $e->course?->title_uz,
                'thumbnail_url' => $e->course?->thumbnail_url,
                'progress'      => (float) $e->progress,
            ])
            ->values();

        $student = Student::where('user_id', $userId)->first();

        $contract = $student
            ? Contract::where('student_id', $student->id)->with('payments')->first()
            : null;

        $contractSummary = null;
        if ($contract) {
            $paidAmount = (float) $contract->payments->where('status', 'paid')->sum('amount');
            $totalAmount = (float) $contract->amount;

            $contractSummary = [
                'status'           => $contract->status,
                'amount'           => $totalAmount,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => max(0, $totalAmount - $paidAmount),
                'paid_percent'     => $totalAmount > 0 ? round(min(100, $paidAmount / $totalAmount * 100), 1) : 0,
            ];
        }

        return Inertia::render('Student/Dashboard', [
            'stats' => [
                'enrollments_active'    => $activeEnrollments->count(),
                'enrollments_completed' => $enrollments->where('status', 'completed')->count(),
                'avg_progress'          => $enrollments->count() ? round($enrollments->avg('progress'), 1) : 0,
                'library_access_count'  => LibraryAccess::where('user_id', $userId)->count(),
            ],
            'continueLearning' => $continueLearning,
            'contract'         => $contractSummary,
        ]);
    }
}
