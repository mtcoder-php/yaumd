<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdmissionRequest;
use App\Models\Applicant;
use App\Models\Faculty;
use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdmissionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Web/Admission/Create', [
            'faculties' => Faculty::where('is_active', true)
                ->with(['directions' => fn($q) => $q->where('is_active', true)])
                ->get(),
            'regions'   => Region::where('is_active', true)
                ->with(['districts' => fn($q) => $q->where('is_active', true)])
                ->get(),
        ]);
    }

    public function store(StoreAdmissionRequest $request)
    {
        $validated = $request->validated();

        // Fayllarni saqlash
        foreach (['passport_file', 'diploma_file', 'diploma_appendix_file'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $folder = $fileKey === 'passport_file' ? 'passports' : 'diplomas';
                $validated[$fileKey] = $request->file($fileKey)->store("applicants/{$folder}", 'public');
            }
        }

        // Ariza raqamini shakllantirish
        $year   = date('Y');
        $count  = Applicant::whereYear('created_at', $year)->count() + 1;
        $number = $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $applicant = Applicant::create([
            ...$validated,
            'application_number' => $number,
            'status'             => 'new',
        ]);

        return redirect()->route('qabul.ariza.success', [
            'number' => $applicant->application_number,
        ]);
    }

    public function success(Request $request): Response
    {
        return Inertia::render('Web/Admission/Success', [
            'number' => $request->query('number'),
        ]);
    }
}
