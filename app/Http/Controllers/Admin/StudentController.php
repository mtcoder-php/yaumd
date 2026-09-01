<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Direction;
use App\Models\Student;
use App\Services\HemisImportService;
use App\Services\StudentAccountService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function __construct(
        private HemisImportService $importService,
        private StudentAccountService $accountService,
    ) {}

    public function index(Request $request): Response
    {
        $query = Student::with(['academicYear', 'direction', 'department'])
            ->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->when($request->filled('direction_id'), fn ($q) => $q->where('direction_id', $request->direction_id))
            ->when($request->filled('course_year'), fn ($q) => $q->where('course_year', $request->course_year))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('hemis_id', 'like', "%{$search}%");
                });
            })
            ->latest();

        return Inertia::render('Admin/Students/Index', [
            'students'     => $query->paginate(20)->withQueryString(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(['id', 'name', 'is_active']),
            'directions'   => Direction::orderBy('name_uz')->get(['id', 'name_uz', 'hemis_code']),
            'filters'      => $request->only(['academic_year_id', 'direction_id', 'course_year', 'status', 'search']),
        ]);
    }

    public function show(int $id): Response
    {
        return Inertia::render('Admin/Students/Show', [
            'student' => Student::with(['academicYear', 'direction.faculty', 'department', 'groups', 'enrollments.course', 'contract'])->findOrFail($id),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Students/Create', [
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(['id', 'name', 'is_active']),
            'directions'    => Direction::orderBy('name_uz')->get(['id', 'name_uz', 'department_id']),
            'departments'   => Department::orderBy('name_uz')->get(['id', 'name_uz']),
        ]);
    }

    public function store(StoreStudentRequest $request)
    {
        $student = Student::create($request->validated());

        if ($student->funding_type === 'contract') {
            $this->createContractForStudent($student);
        }

        $account = $this->accountService->provision($student);

        return redirect()->route('admin.students.index')
            ->with('success', $this->appendAccountNote('Talaba yaratildi!', $account['message']));
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Students/Edit', [
            'student'       => Student::with('contract')->findOrFail($id),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(['id', 'name', 'is_active']),
            'directions'    => Direction::orderBy('name_uz')->get(['id', 'name_uz', 'department_id']),
            'departments'   => Department::orderBy('name_uz')->get(['id', 'name_uz']),
        ]);
    }

    public function update(UpdateStudentRequest $request, int $id)
    {
        $student = Student::findOrFail($id);
        $student->update($request->validated());

        if ($student->funding_type === 'contract') {
            $this->createContractForStudent($student);
        }

        // Talaba avval email/passport bo'lmagani uchun hisobsiz qolgan
        // bo'lishi mumkin — endi to'ldirilgan bo'lsa, shu yerda yaratiladi
        // (metod idempotent, allaqachon hisobi bo'lsa hech narsa qilmaydi).
        $account = $this->accountService->provision($student);

        return redirect()->route('admin.students.index')
            ->with('success', $this->appendAccountNote("Talaba ma'lumotlari yangilandi!", $account['message']));
    }

    public function destroy(int $id)
    {
        Student::findOrFail($id)->delete();

        return back()->with('success', "Talaba o'chirildi!");
    }

    /**
     * Kontrakt asosida (pullik) o'qiyotgan, lekin hali kontrakti bo'lmagan
     * talaba uchun avtomatik kontrakt yaratadi. Talaba Abituriyentlar oqimi
     * orqali kelmagani uchun (qo'lda kiritilgan yoki HEMIS import qilingan)
     * kontrakt bu yerda applicant_id emas, student_id orqali bog'lanadi.
     */
    private function createContractForStudent(Student $student): void
    {
        if (Contract::where('student_id', $student->id)->exists()) {
            return;
        }

        if ($student->applicant_id && Contract::where('applicant_id', $student->applicant_id)->exists()) {
            return;
        }

        Contract::create([
            'student_id'      => $student->id,
            'direction_id'    => $student->direction_id,
            'contract_number' => Contract::generateNumber(),
            'amount'          => $student->direction?->annual_fee ?? 0,
            'payment_type'    => 'contract',
            'status'          => 'draft',
        ]);
    }

    private function appendAccountNote(string $message, ?string $note): string
    {
        return $note ? "{$message} {$note}" : $message;
    }

    public function template()
    {
        return response($this->importService->template(), 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="hemis_talabalar_namuna.xlsx"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'file'             => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'academic_year_id.required' => "O'quv yilini tanlang",
            'file.required'             => 'Fayl yuklang',
            'file.mimes'                => 'Faqat .xlsx, .xls yoki .csv fayl qabul qilinadi',
        ]);

        $result = $this->importService->import($request->file('file'), (int) $request->academic_year_id);

        if ($result['created'] === 0 && $result['updated'] === 0) {
            return back()->withErrors([
                'file' => "Birorta ham talaba import qilinmadi. Namuna shablonni yuklab ko'rib chiqing.",
            ])->with('importErrors', $result['errors']);
        }

        $accountsNote = ($result['accounts_created'] ?? 0) > 0
            ? " {$result['accounts_created']} ta talabaga login-parol yaratildi."
            : '';

        return redirect()->route('admin.students.index')
            ->with('success', "Import yakunlandi: {$result['created']} ta yangi, {$result['updated']} ta yangilandi, {$result['skipped']} ta o'tkazib yuborildi.{$accountsNote}")
            ->with('importErrors', $result['errors']);
    }
}
