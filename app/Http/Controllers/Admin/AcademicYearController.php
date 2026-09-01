<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => AcademicYear::withCount(['students', 'groups'])
                ->orderByDesc('start_date')
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AcademicYears/Create');
    }

    public function store(StoreAcademicYearRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($data['is_active'] ?? false) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }

            AcademicYear::create($data);
        });

        return redirect()->route('admin.academic-years.index')
            ->with('success', "O'quv yili yaratildi!");
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/AcademicYears/Edit', [
            'academicYear' => AcademicYear::findOrFail($id),
        ]);
    }

    public function update(UpdateAcademicYearRequest $request, int $id)
    {
        DB::transaction(function () use ($request, $id) {
            $data = $request->validated();
            $academicYear = AcademicYear::findOrFail($id);

            if (($data['is_active'] ?? false) && ! $academicYear->is_active) {
                AcademicYear::where('id', '!=', $id)->where('is_active', true)->update(['is_active' => false]);
            }

            $academicYear->update($data);
        });

        return redirect()->route('admin.academic-years.index')
            ->with('success', "O'quv yili yangilandi!");
    }

    public function destroy(int $id)
    {
        $academicYear = AcademicYear::withCount(['students', 'groups'])->findOrFail($id);

        if ($academicYear->students_count > 0 || $academicYear->groups_count > 0) {
            return back()->withErrors([
                'error' => "Bu o'quv yiliga {$academicYear->students_count} ta talaba va {$academicYear->groups_count} ta guruh bog'langan. Avval ularni o'chiring!",
            ]);
        }

        $academicYear->delete();

        return back()->with('success', "O'quv yili o'chirildi!");
    }
}
