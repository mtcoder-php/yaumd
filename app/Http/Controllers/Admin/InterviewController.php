<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInterviewRequest;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Applicant::where('status', 'interview')
            ->with(['direction.department', 'interview.interviewer'])
            ->latest();

        if ($request->filled('direction_id')) {
            $query->where('direction_id', $request->direction_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('passport_series', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/Interviews/Index', [
            'applicants' => $query->paginate(20)->withQueryString(),
            'filters'    => $request->only(['direction_id', 'search']),
            'directions' => \App\Models\Direction::where('is_active', true)->get(),
            'stats'      => [
                'total'   => Applicant::where('status', 'interview')->count(),
                'pending' => Applicant::where('status', 'interview')
                    ->whereDoesntHave('interview')->count(),
                'passed'  => Interview::where('result', 'passed')->count(),
                'failed'  => Interview::where('result', 'failed')->count(),
            ],
        ]);
    }

    public function store(StoreInterviewRequest $request)
    {
        $applicant = Applicant::findOrFail($request->applicant_id);

        // Suhbat natijasini saqlash
        Interview::updateOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'interviewed_by' => Auth::id(),
                'result'         => $request->result,
                'notes'          => $request->notes,
                'interviewed_at' => now(),
            ]
        );

        // Natijaga qarab status o'zgartirish
        if ($request->result === 'passed') {
            $applicant->update(['status' => 'tested']);
            $this->createTestSession($applicant);
        } else {
            $applicant->update(['status' => 'rejected']);
        }

        return back()->with('success', $request->result === 'passed'
            ? "Suhbat muvaffaqiyatli o'tdi — test sessiyasi yaratildi!"
            : "Suhbat natijasi: rad etildi!"
        );
    }

    private function createTestSession(Applicant $applicant): void
    {
        if (TestSession::where('applicant_id', $applicant->id)->exists()) {
            return;
        }

        $password = sprintf(
            '%02d.%02d.%d',
            $applicant->birth_day,
            $applicant->birth_month,
            $applicant->birth_year
        );

        TestSession::create([
            'applicant_id'    => $applicant->id,
            'direction_id'    => $applicant->direction_id,
            'language'        => 'uz',
            'foreign_lang'    => 'en',
            'login'           => $applicant->passport_series,
            'password_plain'  => $password,
            'password'        => bcrypt($password),
            'status'          => 'pending',
            'expires_at'      => now()->addDays(30),
            'total_questions' => 90,
        ]);
    }
}
