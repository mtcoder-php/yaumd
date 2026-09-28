<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\ContractPaymentScheduleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin panelidan, jismoniy turniket qurilmasiga umuman tegmasdan,
 * "turniket talabaning qarz/to'lov holatiga qarab ochadimi yo'qmi"
 * mantig'ini bitta talabada sinab ko'rish uchun sodda test sahifasi.
 *
 * Haqiqiy terminal so'rovi (TurnstileController::checkAccess) bilan
 * AYNAN bitta qaror manbasidan — ContractPaymentScheduleService::
 * accessDecisionForStudent() — foydalanadi, shuning uchun bu yerdagi
 * natija haqiqiy turniket natijasi bilan har doim bir xil bo'ladi.
 */
class TurnstileAccessCheckController extends Controller
{
    public function __construct(private ContractPaymentScheduleService $schedule)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Turnstile/AccessCheck');
    }

    /**
     * Ism, talaba raqami, HEMIS ID, JSHSHIR yoki pasport seriyasi bo'yicha
     * talaba qidirish — natijalar orasidan birini tanlab, darhol
     * tekshirish mumkin.
     */
    public function search(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $students = Student::query()
            ->with('direction:id,name_uz')
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('hemis_id', 'like', "%{$search}%")
                    ->orWhere('jshshir', 'like', "%{$search}%")
                    ->orWhere('passport_series', 'like', "%{$search}%");
            })
            ->orderBy('last_name')
            ->limit(15)
            ->get()
            ->map(fn (Student $s) => [
                'id'             => $s->id,
                'name'           => $s->fullName(),
                'student_number' => $s->student_number,
                'hemis_id'       => $s->hemis_id,
                'status'         => $s->status,
                'funding_type'   => $s->funding_type,
                'direction'      => $s->direction?->name_uz,
                'course_year'    => $s->course_year,
            ]);

        return response()->json($students);
    }

    public function check(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
        ]);

        $student = Student::findOrFail($data['student_id']);

        return response()->json($this->schedule->accessDecisionForStudent($student));
    }
}
