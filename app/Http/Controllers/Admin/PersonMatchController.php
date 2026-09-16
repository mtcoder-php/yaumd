<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonMatch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Turniket terminalidan kelgan employeeNo'larni (MatchTurnstilePeople
 * buyrug'i avtomatik taklif qilgan yoki hech narsa topa olmagan holatlarni)
 * admin qo'lda ko'rib chiqib, tasdiqlaydi/rad etadi sahifasi.
 */
class PersonMatchController extends Controller
{
    public function index(Request $request): Response
    {
        $matches = PersonMatch::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($qq) use ($search) {
                    $qq->where('employee_no', 'like', "%{$search}%")
                        ->orWhere('person_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $matches->getCollection()->transform(fn (PersonMatch $match) => $this->presentMatch($match));

        $counts = PersonMatch::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Admin/Turnstile/Matches', [
            'matches' => $matches,
            'counts' => [
                'needs_review' => (int) ($counts[PersonMatch::STATUS_NEEDS_REVIEW] ?? 0),
                'unmatched' => (int) ($counts[PersonMatch::STATUS_UNMATCHED] ?? 0),
                'auto_matched' => (int) ($counts[PersonMatch::STATUS_AUTO_MATCHED] ?? 0),
                'matched' => (int) ($counts[PersonMatch::STATUS_MATCHED] ?? 0),
                'rejected' => (int) ($counts[PersonMatch::STATUS_REJECTED] ?? 0),
            ],
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Admin qo'lda ism qidirib, tegishli talaba/xodimni tanlashi uchun
     * (candidates ro'yxatida yo'q bo'lsa) — jonli qidiruv, AJAX orqali.
     */
    public function searchCandidates(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:student,staff',
            'q' => 'nullable|string|max:100',
        ]);

        $search = trim($data['q'] ?? '');

        if ($search === '') {
            return response()->json([]);
        }

        if ($data['type'] === 'student') {
            $results = Student::query()
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                })
                ->limit(15)
                ->get()
                ->map(fn (Student $s) => [
                    'type' => 'student',
                    'id' => $s->id,
                    'name' => trim("{$s->last_name} {$s->first_name} {$s->middle_name}"),
                    'extra' => $s->student_number,
                ]);
        } else {
            $results = User::query()
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'))
                ->where('full_name', 'like', "%{$search}%")
                ->limit(15)
                ->get()
                ->map(fn (User $u) => [
                    'type' => 'staff',
                    'id' => $u->id,
                    'name' => $u->full_name,
                    'extra' => $u->email,
                ]);
        }

        return response()->json($results->values());
    }

    /**
     * Taklif etilgan nomzodlardan birini bosish ORQALI yoki qo'lda
     * qidirib topilgan yozuvni tanlash orqali — ikkalasi ham shu bitta
     * amaldan foydalanadi.
     */
    public function assign(Request $request, PersonMatch $personMatch): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:student,staff',
            'id' => 'required|integer',
        ]);

        $modelClass = $data['type'] === 'student' ? Student::class : User::class;

        if (! $modelClass::query()->whereKey($data['id'])->exists()) {
            return back()->with('error', 'Tanlangan yozuv topilmadi — ehtimol o\'chirilgan.');
        }

        $personMatch->update([
            'matchable_type' => $data['type'],
            'matchable_id' => $data['id'],
            'status' => PersonMatch::STATUS_MATCHED,
            'confidence' => 1.0,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $personMatch->syncEventsMatch();

        return back()->with('success', "\"{$personMatch->employee_no}\" muvaffaqiyatli moslashtirildi.");
    }

    /**
     * "Bu employeeNo uchun YAUMD'da mos yozuv yo'q" (masalan mehmon,
     * yoki hali tizimga kiritilmagan yangi talaba/xodim) — bu holatda
     * MatchTurnstilePeople --force berilmaguncha uni qayta taklif
     * qilmaydi.
     */
    public function reject(Request $request, PersonMatch $personMatch): RedirectResponse
    {
        $personMatch->update([
            'matchable_type' => null,
            'matchable_id' => null,
            'status' => PersonMatch::STATUS_REJECTED,
            'confidence' => null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $personMatch->syncEventsMatch();

        return back()->with('success', "\"{$personMatch->employee_no}\" rad etildi — endi qayta taklif qilinmaydi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function presentMatch(PersonMatch $match): array
    {
        $matchable = $match->matchable;

        $matchedName = null;
        if ($matchable instanceof Student) {
            $matchedName = trim("{$matchable->last_name} {$matchable->first_name} {$matchable->middle_name}");
        } elseif ($matchable instanceof User) {
            $matchedName = $matchable->full_name;
        }

        return [
            'id' => $match->id,
            'employee_no' => $match->employee_no,
            'person_name' => $match->person_name,
            'status' => $match->status,
            'confidence' => $match->confidence,
            'candidates' => $match->candidates ?? [],
            'matched_type' => $match->matchable_type,
            'matched_name' => $matchedName,
            'reviewer' => $match->reviewer?->full_name,
            'updated_at' => $match->updated_at,
        ];
    }
}
