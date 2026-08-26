<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDirectionRequest;
use App\Http\Requests\UpdateDirectionRequest;
use App\Models\Department;
use App\Models\Direction;
use App\Models\Faculty;
use Inertia\Inertia;
use Inertia\Response;

class DirectionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Directions/Index', [
            'faculties' => Faculty::where('is_active', true)
                ->with(['directions' => fn($q) => $q
                    ->with('department')
                    ->withCount('applicants')])
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Directions/Create', [
            'faculties'   => Faculty::where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }

    public function store(StoreDirectionRequest $request)
    {
        Direction::create($request->validated());

        return redirect()->route('admin.directions.index')
            ->with('success', "Yo'nalish yaratildi!");
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Directions/Edit', [
            'direction'   => Direction::findOrFail($id),
            'faculties'   => Faculty::where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->get(),
        ]);
    }

    public function update(UpdateDirectionRequest $request, int $id)
    {
        Direction::findOrFail($id)->update($request->validated());

        return redirect()->route('admin.directions.index')
            ->with('success', "Yo'nalish yangilandi!");
    }

    public function destroy(int $id)
    {
        $direction = Direction::findOrFail($id);

        if ($direction->applicants()->count() > 0) {
            return back()->withErrors([
                'error' => "Bu yo'nalishda {$direction->applicants()->count()} ta abituriyent bor!"
            ]);
        }

        $direction->delete();
        return back()->with('success', "Yo'nalish o'chirildi!");
    }
}
