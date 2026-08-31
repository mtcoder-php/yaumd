<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdmissionRequest;
use App\Models\Applicant;
use App\Models\Faculty;
use App\Models\Region;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Contract;

class ApplicantController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Applicant::query()
            ->with(['direction.faculty', 'region', 'district'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('education_type')) {
            $query->where('education_type', $request->education_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('passport_series', 'like', "%{$search}%")
                    ->orWhere('application_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/Applicants/Index', [
            'applicants' => $query->paginate(20)->withQueryString(),
            'filters'    => $request->only(['status', 'education_type', 'search']),
            'faculties'  => Faculty::where('is_active', true)->get(),
        ]);
    }

    public function show(int $id): Response
    {
        $applicant = Applicant::with([
            'direction.faculty',
            'region',
            'district',
            'interview.interviewer',
            'testSession',
            'contract.payments',
        ])->findOrFail($id);

        return Inertia::render('Admin/Applicants/Show', [
            'applicant' => $applicant,
        ]);
    }

    public function edit(int $id): Response
    {
        $applicant = Applicant::with(['direction.faculty', 'region', 'district'])
            ->findOrFail($id);

        return Inertia::render('Admin/Applicants/Edit', [
            'applicant' => $applicant,
            'faculties' => Faculty::where('is_active', true)
                ->with(['directions' => fn($q) => $q->where('is_active', true)])
                ->get(),
            'regions'   => Region::where('is_active', true)
                ->with(['districts' => fn($q) => $q->where('is_active', true)])
                ->get(),
        ]);
    }

    public function update(UpdateAdmissionRequest $request, int $id)
    {
        $applicant = Applicant::findOrFail($id);
        $applicant->update($request->validated());

        // Agar yo'nalish o'zgargan bo'lsa — sessiyani ham yangilaymiz
        if ($request->direction_id && $applicant->testSession) {
            $applicant->testSession->update([
                'direction_id' => $request->direction_id,
                'questions'    => null, // savollarni qayta yuklash uchun
            ]);
        }

        return redirect()->route('admin.applicants.show', $id)
            ->with('success', "Ma'lumotlar yangilandi!");
    }



    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:new,accepted,interview,tested,contracted,enrolled,rejected',
        ]);

        $applicant = Applicant::findOrFail($id);
        $oldStatus = $applicant->status;
        $applicant->update(['status' => $request->status]);

        if ($request->status === 'tested' && $oldStatus !== 'tested') {
            $this->createTestSession($applicant);
        }

        // Kontrakt statusiga o'tganda avtomatik kontrakt yaratish
        if ($request->status === 'contracted' && $oldStatus !== 'contracted') {
            $this->createContract($applicant);
        }

        return back()->with('success', 'Status yangilandi!');
    }

    private function createContract(Applicant $applicant): void
    {
        if (Contract::where('applicant_id', $applicant->id)->exists()) {
            return;
        }

        do {
            $number = 'BK' . random_int(100000000, 999999999);
        } while (Contract::withTrashed()->where('contract_number', $number)->exists());

        Contract::create([
            'applicant_id'    => $applicant->id,
            'direction_id'    => $applicant->direction_id,
            'contract_number' => $number,
            'amount'          => $applicant->direction?->annual_fee ?? 0,
            'payment_type'    => 'contract',
            'status'          => 'draft',
        ]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'exists:applicants,id',
            'status' => 'required|in:new,accepted,interview,tested,contracted,enrolled,rejected',
        ]);

        $applicants = Applicant::whereIn('id', $request->ids)->get();

        foreach ($applicants as $applicant) {
            $oldStatus = $applicant->status;
            $applicant->update(['status' => $request->status]);

            if ($request->status === 'tested' && $oldStatus !== 'tested') {
                $this->createTestSession($applicant);
            }
        }

        return back()->with('success', count($request->ids) . ' ta abituriyent statusi yangilandi!');
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
