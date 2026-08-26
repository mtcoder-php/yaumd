<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Departments/Index', [
            'departments' => Department::with(['faculty', 'head'])
                ->withCount('directions')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Departments/Create', [
            'faculties' => Faculty::where('is_active', true)->get(),
            'users'     => User::all(),
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        Department::create($request->validated());

        return redirect()->route('admin.departments.index')
            ->with('success', 'Kafedra yaratildi!');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Departments/Edit', [
            'department' => Department::findOrFail($id),
            'faculties'  => Faculty::where('is_active', true)->get(),
            'users'      => User::all(),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, int $id)
    {
        Department::findOrFail($id)->update($request->validated());

        return redirect()->route('admin.departments.index')
            ->with('success', 'Kafedra yangilandi!');
    }

    public function destroy(int $id)
    {
        $dept = Department::findOrFail($id);

        if ($dept->directions()->count() > 0) {
            return back()->withErrors([
                'error' => "Bu kafedrada {$dept->directions()->count()} ta yo'nalish bor!"
            ]);
        }

        $dept->delete();
        return back()->with('success', "Kafedra o'chirildi!");
    }
}
