<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDirectionSubjectRequest;
use App\Http\Requests\UpdateDirectionSubjectRequest;
use App\Models\DirectionSubject;
use App\Models\Faculty;
use App\Models\Subject;
use Inertia\Inertia;
use Inertia\Response;

class DirectionSubjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/DirectionSubjects/Index', [
            'faculties' => Faculty::where('is_active', true)
                ->with(['departments' => fn($q) => $q
                    ->where('is_active', true)
                    ->with(['directions' => fn($q) => $q
                        ->where('is_active', true)
                        ->with(['subjects' => fn($q) => $q->with('subject')])
                    ])
                ])
                ->get(),
            'subjects' => Subject::where('is_active', true)->get(),
        ]);
    }

    public function store(StoreDirectionSubjectRequest $request)
    {
        DirectionSubject::updateOrCreate(
            [
                'direction_id' => $request->direction_id,
                'subject_id'   => $request->subject_id,
            ],
            [
                'block_type'         => $request->block_type,
                'questions_count'    => $request->questions_count,
                'score_per_question' => $request->score_per_question,
                'is_active'          => true,
            ]
        );

        return back()->with('success', "Fan yo'nalishga biriktirildi!");
    }

    public function update(UpdateDirectionSubjectRequest $request, int $id)
    {
        DirectionSubject::findOrFail($id)->update($request->validated());
        return back()->with('success', 'Yangilandi!');
    }

    public function destroy(int $id)
    {
        DirectionSubject::findOrFail($id)->delete();
        return back()->with('success', "O'chirildi!");
    }
}
