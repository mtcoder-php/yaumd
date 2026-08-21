<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    public function index(): Response
    {
        $subjects = Subject::withCount([
            'questions',
            'questions as questions_uz_count' => fn($q) => $q->where('language', 'uz'),
            'questions as questions_ru_count' => fn($q) => $q->where('language', 'ru'),
        ])
            ->orderBy('name_uz')
            ->get();

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Subjects/Create');
    }

    public function store(StoreSubjectRequest $request)
    {
        Subject::create($request->validated());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Fan muvaffaqiyatli yaratildi!');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Subjects/Edit', [
            'subject' => Subject::findOrFail($id),
        ]);
    }

    public function update(UpdateSubjectRequest $request, int $id)
    {
        Subject::findOrFail($id)->update($request->validated());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Fan yangilandi!');
    }

    public function destroy(int $id)
    {
        $subject = Subject::findOrFail($id);

        if ($subject->questions()->count() > 0) {
            return back()->with('error', "'{$subject->name_uz}' fanini o'chirib bo'lmaydi — ichida {$subject->questions()->count()} ta savol mavjud!");
        }

        $subject->delete();
        return back()->with('success', "Fan o'chirildi!");
    }
}
