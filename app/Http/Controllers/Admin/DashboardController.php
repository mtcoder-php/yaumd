<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        // Shartnoma/to'lov ko'rsatkichlari — referensdagi (billing.e-edu.uz)
        // Dashboard'idagi "Tasdiqlangan shartnomalar", "Shartnoma summasi",
        // "Tushgan to'lovlar", "Bekor qilingan" kartalariga mos, haqiqiy
        // bazadan hisoblanadi (o'ylab topilgan raqam emas).
        $contractsConfirmed = Contract::whereIn('status', ['signed', 'paid'])->count();
        $contractsCancelled = Contract::where('status', 'cancelled')->count();
        $contractsAmount    = (float) Contract::whereIn('status', ['signed', 'paid'])->sum('amount');
        $paymentsTotal      = (float) Payment::where('status', 'paid')->sum('amount');

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'applicants_total'    => Applicant::count(),
                'applicants_new'      => Applicant::where('status', 'new')->count(),
                'applicants_today'    => Applicant::whereDate('created_at', today())->count(),
                'enrolled'            => Applicant::where('status', 'enrolled')->count(),
                'students'            => Student::count(),
                'contracts'           => Contract::count(),
                'contracts_confirmed' => $contractsConfirmed,
                'contracts_cancelled' => $contractsCancelled,
                'contracts_amount'    => $contractsAmount,
                'payments_total'      => $paymentsTotal,
            ],
            'academic_year'   => $activeAcademicYear?->name,
            'contracts_trend' => $this->contractsTrend(),
            'hemis_pipeline'  => $this->hemisPipeline(),
            'student_breakdown' => [
                'study_form' => $this->studentsByStudyForm(),
                'course_year' => $this->studentsByCourseYear(),
                'degree'      => $this->studentsByDegree(),
            ],
            'top_faculties'   => $this->topFaculties(),
            'top_directions'  => $this->topDirections(),
            'regional_demographics' => $this->regionalDemographics(),
            'financial_pipeline'    => [
                'contract_amount' => $contractsAmount,
                'paid_amount'     => $paymentsTotal,
                'remaining_amount' => max($contractsAmount - $paymentsTotal, 0),
            ],
            'payment_channels' => $this->paymentChannels(),
            // "FIFO" — navbat tartibida (eng birinchi kelib tushgan
            // ko'rib chiqilmagan ariza birinchi bo'lib) ko'rsatiladi,
            // referensdagi "Yangi arizalar (FIFO)" jadvaliga mos —
            // eng SO'NGGI emas, aksincha eng ESKI kutayotgan arizalar,
            // chunki bu jadval xodimga "navbatda kim kutmoqda" degan
            // savolga javob beradi.
            'fifo_applicants' => Applicant::with('direction')
                ->where('status', 'new')
                ->oldest()
                ->take(10)
                ->get(),
        ]);
    }

    /**
     * Talaba → HEMIS → shartnoma konversiya zanjiri (referensdagi
     * "Tasdiqlangan arizalar va HEMIS" / "HEMIS talabalar va shartnoma"
     * kartalariga mos). Talaba jadvalidagi 'hemis_id' (HEMIS'dan
     * sinxronlangan bo'lsa to'ldiriladi) va shartnoma bog'lanishi orqali
     * hisoblanadi — ikkalasi ham haqiqiy ustun/munosabat, o'ylab topilgan
     * emas.
     */
    private function hemisPipeline(): array
    {
        $total      = Student::count();
        $withHemis  = Student::whereNotNull('hemis_id')->count();
        $withContract = Student::whereHas('contract')->count();

        return [
            'students_total'        => $total,
            'students_with_hemis'   => $withHemis,
            'students_without_hemis'=> $total - $withHemis,
            'students_with_contract'   => $withContract,
            'students_without_contract'=> $total - $withContract,
        ];
    }

    /**
     * Berilgan tartib bo'yicha guruhlangan sonlarni qaytaradi — frontend
     * donut/diagramma rangi har doim bir xil kategoriyaga (masalan
     * "Kunduzgi" har doim birinchi/indigo) mos kelishi uchun, DB'dagi
     * tasodifiy guruhlash tartibiga emas, shu FIXED tartibga tayanadi.
     */
    private function orderedCounts(string $column, array $order): array
    {
        $counts = Student::selectRaw("{$column}, count(*) as count")
            ->groupBy($column)
            ->pluck('count', $column);

        $result = [];
        foreach ($order as $key) {
            if (isset($counts[$key])) {
                $result[$key] = (int) $counts[$key];
            }
        }

        return $result;
    }

    private function studentsByStudyForm(): array
    {
        return $this->orderedCounts('study_form', ['full_time', 'evening', 'distance']);
    }

    private function studentsByCourseYear(): array
    {
        return Student::selectRaw('course_year as label, count(*) as count')
            ->groupBy('course_year')
            ->orderBy('course_year')
            ->pluck('count', 'label')
            ->toArray();
    }

    private function studentsByDegree(): array
    {
        return $this->orderedCounts('degree', ['bachelor', 'master']);
    }

    /**
     * TOP fakultetlar — tasdiqlangan shartnomalar soni bo'yicha (yo'nalish
     * orqali fakultetga bog'lanadi).
     */
    private function topFaculties(): array
    {
        return Contract::whereIn('contracts.status', ['signed', 'paid'])
            ->join('directions', 'directions.id', '=', 'contracts.direction_id')
            ->join('faculties', 'faculties.id', '=', 'directions.faculty_id')
            ->selectRaw('faculties.name_uz as label, count(*) as count')
            ->groupBy('faculties.id', 'faculties.name_uz')
            ->orderByDesc('count')
            ->take(8)
            ->get()
            ->toArray();
    }

    /**
     * TOP yo'nalishlar (mutaxassisliklar) — arizalar soni bo'yicha.
     */
    private function topDirections(): array
    {
        return Applicant::join('directions', 'directions.id', '=', 'applicants.direction_id')
            ->selectRaw('directions.name_uz as label, count(*) as count')
            ->groupBy('directions.id', 'directions.name_uz')
            ->orderByDesc('count')
            ->take(10)
            ->get()
            ->toArray();
    }

    /**
     * Hududiy demografiya — abituriyentlar qaysi viloyatdan.
     */
    private function regionalDemographics(): array
    {
        return Applicant::join('regions', 'regions.id', '=', 'applicants.region_id')
            ->selectRaw('regions.name_uz as label, count(*) as count')
            ->groupBy('regions.id', 'regions.name_uz')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    /**
     * To'lov kanali bo'yicha (Click/Payme/naqd-"Xazna") tranzaksiyalar
     * soni va jami summa — referensdagi "TO'LOV KANALI" jadvaliga mos.
     * Ulush foizi frontend'da jami to'langan summaga nisbatan hisoblanadi.
     */
    private function paymentChannels(): array
    {
        return Payment::where('status', 'paid')
            ->selectRaw('provider, count(*) as tx_count, sum(amount) as total')
            ->groupBy('provider')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'provider'  => $row->provider,
                'tx_count'  => (int) $row->tx_count,
                'total'     => (float) $row->total,
            ])
            ->toArray();
    }

    /**
     * "Kunlik shartnomalar" grafigi uchun uch xil davr granularityda
     * (kun/oy/yil) tasdiqlangan va bekor qilingan shartnomalar soni.
     *
     * MUHIM: Contract jadvalida shartnoma qachon bekor qilinganini
     * bildiruvchi alohida ustun (masalan "cancelled_at") hali yo'q —
     * shuning uchun ikkala qator ham 'created_at' (shartnoma YARATILGAN
     * sana) bo'yicha guruhlanadi: "shu davrda yaratilgan shartnomalardan
     * nechtasi hozir tasdiqlangan/bekor qilingan holatda" ma'nosida.
     */
    private function contractsTrend(): array
    {
        $daily = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));
        $monthly = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));
        $yearly = collect(range(4, 0))->map(fn ($i) => (string) now()->subYears($i)->year);

        return [
            'daily'   => $this->contractPeriodCounts('DATE(created_at)', now()->subDays(13)->startOfDay(), $daily),
            'monthly' => $this->contractPeriodCounts("DATE_FORMAT(created_at, '%Y-%m')", now()->subMonths(11)->startOfMonth(), $monthly),
            'yearly'  => $this->contractPeriodCounts('YEAR(created_at)', now()->subYears(4)->startOfYear(), $yearly),
        ];
    }

    private function contractPeriodCounts(string $dateExpr, Carbon $from, Collection $labels): array
    {
        $toKeyed = function (string $status) use ($dateExpr, $from) {
            return Contract::selectRaw("{$dateExpr} as period, count(*) as count")
                ->where('status', $status)
                ->where('created_at', '>=', $from)
                ->groupBy('period')
                ->pluck('count', 'period')
                ->mapWithKeys(fn ($count, $period) => [(string) $period => (int) $count]);
        };

        // 'signed' va 'paid' ikkalasi ham "tasdiqlangan" hisoblanadi.
        $confirmedSigned = $toKeyed('signed');
        $confirmedPaid   = $toKeyed('paid');
        $cancelled       = $toKeyed('cancelled');

        return $labels->map(fn ($key) => [
            'label'     => $key,
            'confirmed' => ($confirmedSigned[$key] ?? 0) + ($confirmedPaid[$key] ?? 0),
            'cancelled' => $cancelled[$key] ?? 0,
        ])->values()->all();
    }
}
