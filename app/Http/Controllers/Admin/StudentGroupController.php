<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentGroupRequest;
use App\Http\Requests\UpdateStudentGroupRequest;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Direction;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentGroupController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StudentGroup::with(['academicYear', 'direction', 'department'])
            ->withCount('students')
            ->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->when($request->filled('direction_id'), fn ($q) => $q->where('direction_id', $request->direction_id))
            ->when($request->filled('degree'), fn ($q) => $q->where('degree', $request->degree))
            ->when($request->filled('course_year'), fn ($q) => $q->where('course_year', $request->course_year))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('hemis_id', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        return Inertia::render('Admin/StudentGroups/Index', [
            'groups'        => $query->paginate(20)->withQueryString(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(['id', 'name', 'is_active']),
            'directions'    => Direction::orderBy('name_uz')->get(['id', 'name_uz']),
            'filters'       => $request->only(['academic_year_id', 'direction_id', 'degree', 'course_year', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/StudentGroups/Create', $this->formData());
    }

    public function store(StoreStudentGroupRequest $request)
    {
        StudentGroup::create($request->validated());

        return redirect()->route('admin.student-groups.index')
            ->with('success', 'Guruh yaratildi!');
    }

    public function show(int $id): Response
    {
        $group = StudentGroup::with(['academicYear', 'direction', 'department', 'headTeacher'])
            ->withCount('students')
            ->findOrFail($id);

        $members = $group->students()
            ->orderBy('last_name')
            ->get(['students.id', 'students.first_name', 'students.last_name', 'students.middle_name', 'students.student_number', 'students.status']);

        // Shu guruhga qo'shsa bo'ladigan talabalar: bir xil yo'nalishdagi, hali shu guruhga a'zo bo'lmagan talabalar
        $availableStudents = Student::where('direction_id', $group->direction_id)
            ->whereDoesntHave('groups', fn ($q) => $q->where('student_groups.id', $group->id))
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'middle_name', 'student_number']);

        return Inertia::render('Admin/StudentGroups/Show', [
            'group'             => $group,
            'members'           => $members,
            'availableStudents' => $availableStudents,
        ]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/StudentGroups/Edit', array_merge(
            $this->formData(),
            ['group' => StudentGroup::findOrFail($id)],
        ));
    }

    public function update(UpdateStudentGroupRequest $request, int $id)
    {
        $group = StudentGroup::findOrFail($id);
        $group->update($request->validated());

        return redirect()->route('admin.student-groups.index')
            ->with('success', 'Guruh yangilandi!');
    }

    public function destroy(int $id)
    {
        $group = StudentGroup::withCount('students')->findOrFail($id);

        if ($group->students_count > 0) {
            return back()->with('error', "Bu guruhda {$group->students_count} ta talaba bor. Avval ularni guruhdan chiqaring!");
        }

        $group->delete();

        return back()->with('success', "Guruh o'chirildi!");
    }

    public function addStudent(Request $request, int $id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $group = StudentGroup::findOrFail($id);

        if ($group->students()->where('students.id', $request->student_id)->exists()) {
            return back()->with('error', 'Bu talaba allaqachon guruhda!');
        }

        $group->students()->attach($request->student_id);

        return back()->with('success', "Talaba guruhga qo'shildi!");
    }

    public function removeStudent(int $id, int $studentId)
    {
        $group = StudentGroup::findOrFail($id);
        $group->students()->detach($studentId);

        return back()->with('success', "Talaba guruhdan chiqarildi!");
    }

    private function formData(): array
    {
        return [
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(['id', 'name', 'is_active']),
            'directions'    => Direction::orderBy('name_uz')->get(['id', 'name_uz', 'department_id', 'degree']),
            'departments'   => Department::orderBy('name_uz')->get(['id', 'name_uz']),
            'teachers'      => User::role('teacher')->orderBy('full_name')->get(['id', 'full_name']),
        ];
    }
}
