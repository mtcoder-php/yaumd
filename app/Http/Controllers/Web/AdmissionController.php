<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Direction;
use App\Models\Faculty;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdmissionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Web/Admission/Create', [
            'faculties'  => Faculty::where('is_active', true)
                ->with(['directions' => fn($q) => $q->where('is_active', true)])
                ->get(),
            'regions'    => Region::where('is_active', true)
                ->with(['districts' => fn($q) => $q->where('is_active', true)])
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'education_type'  => 'required|in:bachelor,master,transfer,second',
            'direction_id'    => 'required|exists:directions,id',
            'study_form'      => 'required|in:full_time,evening,distance',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'middle_name'     => 'required|string|max:100',
            'birth_year'      => 'required|integer|min:1950|max:' . (date('Y') - 14),
            'birth_month'     => 'required|integer|min:1|max:12',
            'birth_day'       => 'required|integer|min:1|max:31',
            'gender'          => 'required|in:male,female',
            'passport_series' => 'required|string|max:9',
            'region_id'       => 'required|exists:regions,id',
            'district_id'     => 'required|exists:districts,id',
            'phone'           => 'required|string|max:15',
            'extra_phone'     => 'nullable|string|max:15',
        ], [
            'education_type.required'  => "Ta'lim turini tanlang",
            'direction_id.required'    => "Yo'nalishni tanlang",
            'study_form.required'      => "Ta'lim shaklini tanlang",
            'first_name.required'      => "Familiyangizni kiriting",
            'last_name.required'       => "Ismingizni kiriting",
            'middle_name.required'     => "Otangizning ismini kiriting",
            'birth_year.required'      => "Tug'ilgan yilni kiriting",
            'birth_month.required'     => "Tug'ilgan oyni tanlang",
            'birth_day.required'       => "Tug'ilgan kunni kiriting",
            'gender.required'          => "Jinsingizni tanlang",
            'passport_series.required' => "Pasport seriyasini kiriting",
            'region_id.required'       => "Viloyatni tanlang",
            'district_id.required'     => "Tumanni tanlang",
            'phone.required'           => "Telefon raqamingizni kiriting",
        ]);

        $year   = date('Y');
        $count  = Applicant::whereYear('created_at', $year)->count() + 1;
        $number = $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $applicant = Applicant::create([
            ...$validated,
            'application_number' => $number,
            'status'             => 'new',
        ]);

        return redirect()->route('qabul.ariza.success', [
            'number' => $applicant->application_number
        ]);
    }

    public function success(Request $request): Response
    {
        return Inertia::render('Web/Admission/Success', [
            'number' => $request->query('number'),
        ]);
    }
}
