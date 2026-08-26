<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Faculty;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class FacultyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Faculties/Index', [
            'faculties' => Faculty::withCount(['directions', 'departments'])
                ->with('dean')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Faculties/Create', [
            'deans' => User::role(['admin', 'teacher'])->get(),
        ]);
    }

    public function store(StoreFacultyRequest $request)
    {
        Faculty::create($request->validated());

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Fakultet yaratildi!');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Faculties/Edit', [
            'faculty' => Faculty::findOrFail($id),
            'deans'   => User::role(['admin', 'teacher'])->get(),
        ]);
    }

    public function update(UpdateFacultyRequest $request, int $id)
    {
        Faculty::findOrFail($id)->update($request->validated());

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Fakultet yangilandi!');
    }

    public function destroy(int $id)
    {
        $faculty = Faculty::findOrFail($id);

        if ($faculty->directions()->count() > 0) {
            return back()->withErrors([
                'error' => "Bu fakultetda {$faculty->directions()->count()} ta yo'nalish bor. Avval yo'nalishlarni o'chiring!"
            ]);
        }

        $faculty->delete();
        return back()->with('success', "Fakultet o'chirildi!");
    }
}
