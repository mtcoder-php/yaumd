<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\Course;
use App\Models\LibraryBook;
use App\Models\Contract;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    // MUHIM: '/admin/dashboard' bitta umumiy marshrut — barcha rollar shu
    // yerga tushadi (permission talab qilinmaydi). Bu yerdagi statistika
    // (abituriyentlar, shartnomalar soni va h.k.) FAQAT admin/qabul/moliya
    // xodimlariga tegishli — talabaga bularning hech biri kerak emas va
    // tushunarsiz bo'ladi. Shu sababli faqat "student" rolidagi (boshqa
    // hech qanday xodim roli bo'lmagan) foydalanuvchi uchun butunlay
    // boshqa, alohida StudentDashboardController'ga yo'naltiramiz.
    private const STAFF_ROLES = ['super-admin', 'admin', 'admission', 'teacher', 'tutor', 'finance', 'librarian'];

    public function __construct(private StudentDashboardController $studentDashboard)
    {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasRole('student') && ! $user->hasAnyRole(self::STAFF_ROLES)) {
            return $this->studentDashboard->index($request);
        }

        // Abituriyentlar statistikasi
        $applicantStats = Applicant::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Oxirgi 7 kunlik arizalar
        $weeklyApplicants = Applicant::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date'  => $item->date,
                'count' => $item->count,
            ]);

        // Ta'lim turi bo'yicha
        $byEducationType = Applicant::selectRaw('education_type, count(*) as count')
            ->groupBy('education_type')
            ->pluck('count', 'education_type')
            ->toArray();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'applicants_total'   => Applicant::count(),
                'applicants_new'     => Applicant::where('status', 'new')->count(),
                'applicants_today'   => Applicant::whereDate('created_at', today())->count(),
                'enrolled'           => Applicant::where('status', 'enrolled')->count(),
                'students'           => Student::count(),
                'contracts'          => Contract::count(),
            ],
            'applicant_by_status'   => $applicantStats,
            'applicant_by_type'     => $byEducationType,
            'weekly_applicants'     => $weeklyApplicants,
            'recent_applicants' => Applicant::with('direction')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
