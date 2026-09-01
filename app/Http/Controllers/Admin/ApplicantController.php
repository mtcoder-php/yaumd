<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdmissionRequest;
use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\Faculty;
use App\Models\Region;
use App\Models\Student;
use App\Models\TestSession;
use App\Services\StudentAccountService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Contract;

class ApplicantController extends Controller
{
    public function __construct(private StudentAccountService $accountService) {}

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

        $applicant = Applicant::with(['direction', 'contract'])->findOrFail($id);
        $oldStatus = $applicant->status;
        $applicant->update(['status' => $request->status]);

        if ($request->status === 'tested' && $oldStatus !== 'tested') {
            $this->createTestSession($applicant);
        }

        // Kontrakt statusiga o'tganda avtomatik kontrakt yaratish
        if ($request->status === 'contracted' && $oldStatus !== 'contracted') {
            $this->createContract($applicant);
            $applicant->load('contract');
        }

        // Ro'yxatga olindi statusiga o'tganda abituriyentni avtomatik
        // Talabalar jadvaliga qo'shamiz
        if ($request->status === 'enrolled' && $oldStatus !== 'enrolled') {
            $result = $this->createStudentFromApplicant($applicant);

            if (! $result['created'] && $result['message']) {
                return back()->with('error', $result['message']);
            }

            $message = "Status yangilandi! Talaba Talabalar jadvaliga qo'shildi.";
            if ($result['message']) {
                $message .= ' ' . $result['message'];
            }

            return back()->with('success', $message);
        }

        return back()->with('success', 'Status yangilandi!');
    }

    private function createContract(Applicant $applicant): void
    {
        if (Contract::where('applicant_id', $applicant->id)->exists()) {
            return;
        }

        Contract::create([
            'applicant_id'    => $applicant->id,
            'direction_id'    => $applicant->direction_id,
            'contract_number' => Contract::generateNumber(),
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

        $applicants = Applicant::with(['direction', 'contract'])->whereIn('id', $request->ids)->get();
        $warnings = [];

        foreach ($applicants as $applicant) {
            $oldStatus = $applicant->status;
            $applicant->update(['status' => $request->status]);

            if ($request->status === 'tested' && $oldStatus !== 'tested') {
                $this->createTestSession($applicant);
            }

            if ($request->status === 'enrolled' && $oldStatus !== 'enrolled') {
                $result = $this->createStudentFromApplicant($applicant);
                if ($result['message']) {
                    $warnings[] = $result['message'];
                }
            }
        }

        $message = count($request->ids) . ' ta abituriyent statusi yangilandi!';

        if ($warnings) {
            return back()->with('success', $message)->with('error', implode(' | ', $warnings));
        }

        return back()->with('success', $message);
    }

    /**
     * "Ro'yxatga olindi" (enrolled) statusiga o'tgan abituriyentni Talabalar
     * jadvaliga (students) ko'chiradi. Applicant.direction'dagi 'degree'
     * qiymati aynan shu yo'nalish uchun to'g'ri bo'lgani sababli (Yo'nalish
     * o'ziga xos ravishda bakalavr yoki magistr bo'ladi), degree'ni
     * education_type emas, direction'dan olamiz — bu ko'chirilgan (transfer)
     * yoki ikkinchi oliy toifasidagi abituriyentlar uchun ham to'g'ri ishlaydi.
     *
     * @return array{created: bool, message: ?string}
     */
    private function createStudentFromApplicant(Applicant $applicant): array
    {
        // Allaqachon talabaga aylantirilgan bo'lsa — qayta yaratmaymiz
        if (Student::where('applicant_id', $applicant->id)->exists()) {
            return ['created' => false, 'message' => null];
        }

        if (! $applicant->direction_id) {
            return [
                'created' => false,
                'message' => "{$applicant->last_name} {$applicant->first_name}: yo'nalishi belgilanmagan, talaba yaratilmadi. Avval abituriyentga yo'nalish tayinlang.",
            ];
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        if (! $academicYear) {
            return [
                'created' => false,
                'message' => "Joriy (faol) o'quv yili topilmadi. Akademik yillar bo'limida joriy o'quv yilini faollashtiring, so'ng statusni qayta saqlang.",
            ];
        }

        $direction = $applicant->direction;

        // Talaba raqami sifatida shu abituriyentga tuzilgan kontrakt raqamidan
        // foydalanamiz (mas. "BK885863338") — bu talaba uchun allaqachon
        // tanish, hujjatlarda ko'rsatiladigan yagona raqam. Juda kam ehtimol
        // bo'lsa-da, band bo'lib qolgan taqdirda bo'sh qoldiramiz — admin
        // Talaba tahrirlash sahifasida qo'lda to'ldiradi.
        $studentNumber = $applicant->contract?->contract_number;
        if ($studentNumber && Student::where('student_number', $studentNumber)->exists()) {
            $studentNumber = null;
        }

        $student = Student::create([
            'applicant_id'     => $applicant->id,
            'academic_year_id' => $academicYear->id,
            'direction_id'     => $applicant->direction_id,
            'department_id'    => $direction?->department_id,
            'student_number'   => $studentNumber,
            'first_name'       => $applicant->first_name,
            'last_name'        => $applicant->last_name,
            'middle_name'      => $applicant->middle_name,
            'passport_series'  => $applicant->passport_series,
            'jshshir'          => $applicant->jshshir,
            'phone'            => $applicant->phone,
            'email'            => $applicant->email,
            'birth_day'        => $applicant->birth_day,
            'birth_month'      => $applicant->birth_month,
            'birth_year'       => $applicant->birth_year,
            'gender'           => $applicant->gender,
            'degree'           => $direction?->degree ?? ($applicant->education_type === 'master' ? 'master' : 'bachelor'),
            'study_form'       => $applicant->study_form,
            'course_year'      => 1,
            'status'           => 'active',
            // Kontrakt bosqichi (contracted) allaqachon o'tilgan bo'lsa —
            // demak bu kontrakt (pullik) talaba; aks holda grant deb
            // belgilanadi (admin keyin xohlasa Talaba tahrirlashda o'zgartira oladi)
            'funding_type'     => $applicant->contract ? 'contract' : 'grant',
            'address'          => $this->composeStudentAddress($applicant),
            'user_id'          => $applicant->user_id,
        ]);

        // Mavjud kontraktni yangi talaba yozuviga ham bog'laymiz — shunda
        // Talaba profilida ($student->contract) kontrakt qaysi yo'l bilan
        // (Abituriyent oqimi yoki to'g'ridan-to'g'ri) yaratilganidan qat'i
        // nazar bir xilda ko'rinadi.
        if ($applicant->contract) {
            $applicant->contract->update(['student_id' => $student->id]);
        }

        // Ko'chirilgan (transfer) yoki ikkinchi oliy toifasidagi abituriyentlar
        // har doim ham 1-kursdan boshlamaydi — bu avtomatik yaratishda
        // aniqlab bo'lmaydigan narsa, shuning uchun adminni ogohlantiramiz
        $note = in_array($applicant->education_type, ['transfer', 'second'], true)
            ? "\"{$student->last_name} {$student->first_name}\" — \"{$applicant->education_type}\" toifasi bo'lgani uchun kursini tekshirib, kerak bo'lsa Talaba tahrirlash sahifasida to'g'irlang (hozircha 1-kurs qilib qo'yildi)."
            : null;

        // Talaba uchun login (email) + parol (passport seriya) bilan tizimga
        // kirish hisobini avtomatik yaratamiz (email/passport bo'lmasa —
        // ogohlantirish bilan o'tkazib yuboriladi, admin keyin to'ldirganda
        // Talaba tahrirlashni saqlashda qayta urinilib yaratiladi).
        $account = $this->accountService->provision($student);

        $notes = array_filter([$note, $account['message']]);

        return ['created' => true, 'message' => $notes ? implode(' ', $notes) : null];
    }

    private function composeStudentAddress(Applicant $applicant): ?string
    {
        $parts = array_filter([
            $applicant->region?->name_uz,
            $applicant->district?->name_uz,
            $applicant->address,
        ]);

        return $parts ? implode(', ', $parts) : null;
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
