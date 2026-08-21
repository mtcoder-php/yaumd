<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestQuestionRequest;
use App\Http\Requests\UpdateTestQuestionRequest;
use App\Models\Subject;
use App\Models\TestQuestion;
use App\Services\TestImportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestQuestionController extends Controller
{
    public function __construct(private TestImportService $importService) {}

    public function index(Request $request, int $id): Response
    {
        $subject = Subject::findOrFail($id);
        $lang    = $request->query('lang');

        $query = TestQuestion::where('subject_id', $id)->latest();

        if ($lang) {
            $query->where('language', $lang);
        }

        return Inertia::render('Admin/Questions/Index', [
            'subject'    => $subject,
            'questions'  => $query->paginate(20)->withQueryString(),
            'activeLang' => $lang,
            'uzCount'    => TestQuestion::where('subject_id', $id)->where('language', 'uz')->count(),
            'ruCount'    => TestQuestion::where('subject_id', $id)->where('language', 'ru')->count(),
        ]);
    }

    public function create(int $id): Response
    {
        return Inertia::render('Admin/Questions/Create', [
            'subject' => Subject::findOrFail($id),
        ]);
    }

    public function store(StoreTestQuestionRequest $request, int $id)
    {
        $data               = $request->validated();
        $data['subject_id'] = $id;

        TestQuestion::create($data);

        return redirect()->route('admin.subjects.questions.index', $id)
            ->with('success', 'Savol yaratildi!');
    }

    public function edit(int $id, int $qId): Response
    {
        $question = TestQuestion::where('subject_id', $id)->findOrFail($qId);
        $question->makeVisible('correct_answer');

        return Inertia::render('Admin/Questions/Edit', [
            'subject'  => Subject::findOrFail($id),
            'question' => $question,
        ]);
    }

    public function update(UpdateTestQuestionRequest $request, int $id, int $qId)
    {
        $question           = TestQuestion::where('subject_id', $id)->findOrFail($qId);
        $data               = $request->validated();
        $data['subject_id'] = $id;
        $question->update($data);

        return redirect()->route('admin.subjects.questions.index', $id)
            ->with('success', 'Savol yangilandi!');
    }

    public function destroy(int $id, int $qId)
    {
        TestQuestion::where('subject_id', $id)->findOrFail($qId)->delete();
        return back()->with('success', "Savol o'chirildi!");
    }

    public function template(int $id)
    {
        return response($this->importService->template(), 200, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="test_namuna.txt"',
        ]);
    }

    public function import(Request $request, int $id)
    {
        $request->validate([
            'language' => 'required|in:uz,ru',
            'file'     => 'required|file|mimes:txt,docx|max:10240',
        ], [
            'language.required' => 'Tilni tanlang',
            'file.required'     => 'Fayl yuklang',
            'file.mimes'        => 'Faqat .txt yoki .docx fayl qabul qilinadi',
        ]);

        $count = $this->importService->import(
            $request->file('file'),
            $id,
            $request->language
        );

        if ($count === 0) {
            return back()->withErrors([
                'file' => "Fayl formatida xatolik! Namuna shablonni yuklab ko'rib chiqing."
            ]);
        }

        return redirect()->route('admin.subjects.questions.index', $id)
            ->with('success', "{$count} ta savol muvaffaqiyatli yuklandi!");
    }
}
