<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Direction;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Applicant::query()
            ->with(['direction.faculty', 'region', 'district'])
            ->latest();

        // Filter: status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: education_type
        if ($request->filled('education_type')) {
            $query->where('education_type', $request->education_type);
        }

        // Filter: search
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
        $applicant = Applicant::with(['direction.faculty', 'region', 'district'])
            ->findOrFail($id);

        return Inertia::render('Admin/Applicants/Show', [
            'applicant' => $applicant,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:new,accepted,interview,tested,contracted,enrolled,rejected',
        ]);

        $applicant = Applicant::findOrFail($id);
        $applicant->update(['status' => $request->status]);

        return back()->with('success', 'Status yangilandi!');
    }
}
