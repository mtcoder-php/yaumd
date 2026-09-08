<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\CommunicationLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRM — Muloqot tarixi (call log). {type}/{id} qarzdorlar ro'yxatidagi
 * shaxsni bildiradi — 'student' yoki 'applicant', Contract::person orqali
 * aniqlangan (qarang CrmDebtorController::debtorRows()).
 */
class CommunicationLogController extends Controller
{
    private const SUBJECT_MODELS = [
        'student'   => Student::class,
        'applicant' => Applicant::class,
    ];

    public function show(string $type, int $id): Response
    {
        $modelClass = $this->resolveModelClass($type);
        $subject = $modelClass::findOrFail($id);

        $logs = CommunicationLog::where('subject_type', $modelClass)
            ->where('subject_id', $id)
            ->with('creator:id,full_name')
            ->orderByDesc('occurred_at')
            ->get();

        return Inertia::render('Admin/Crm/Contact', [
            'subjectType' => $type,
            'subjectId'   => $id,
            'person'      => [
                'full_name' => trim("{$subject->last_name} {$subject->first_name} {$subject->middle_name}"),
                'phone'     => $subject->phone,
                'email'     => $subject->email,
            ],
            'logs' => $logs,
        ]);
    }

    public function store(Request $request, string $type, int $id)
    {
        $modelClass = $this->resolveModelClass($type);
        $modelClass::findOrFail($id);

        $validated = $request->validate([
            'type'        => 'required|in:call,email,meeting,note',
            'direction'   => 'nullable|in:incoming,outgoing',
            'summary'     => 'required|string|max:2000',
            'occurred_at' => 'nullable|date',
        ], [
            'summary.required' => "Mazmunini yozing",
        ]);

        CommunicationLog::create([
            'subject_type' => $modelClass,
            'subject_id'   => $id,
            'type'         => $validated['type'],
            'direction'    => $validated['direction'] ?? null,
            'summary'      => $validated['summary'],
            'occurred_at'  => $validated['occurred_at'] ?? now(),
            'created_by'   => $request->user()->id,
        ]);

        return back()->with('success', 'Muloqot tarixiga yozildi!');
    }

    private function resolveModelClass(string $type): string
    {
        abort_unless(isset(self::SUBJECT_MODELS[$type]), 404);

        return self::SUBJECT_MODELS[$type];
    }
}
