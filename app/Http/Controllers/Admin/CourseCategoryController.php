<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseCategoryRequest;
use App\Http\Requests\UpdateCourseCategoryRequest;
use App\Models\CourseCategory;
use Inertia\Inertia;
use Inertia\Response;

class CourseCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/CourseCategories/Index', [
            'categories' => CourseCategory::with('parent')
                ->withCount('courses')
                ->orderBy('order')
                ->orderBy('name_uz')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CourseCategories/Create', [
            'categories' => CourseCategory::orderBy('name_uz')->get(['id', 'name_uz']),
        ]);
    }

    public function store(StoreCourseCategoryRequest $request)
    {
        CourseCategory::create($request->validated());

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Kategoriya yaratildi!');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/CourseCategories/Edit', [
            'category'   => CourseCategory::findOrFail($id),
            'categories' => CourseCategory::where('id', '!=', $id)->orderBy('name_uz')->get(['id', 'name_uz']),
        ]);
    }

    public function update(UpdateCourseCategoryRequest $request, int $id)
    {
        CourseCategory::findOrFail($id)->update($request->validated());

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Kategoriya yangilandi!');
    }

    public function destroy(int $id)
    {
        $category = CourseCategory::withCount(['courses', 'children'])->findOrFail($id);

        if ($category->courses_count > 0 || $category->children_count > 0) {
            return back()->with('error', "Bu kategoriyada {$category->courses_count} ta kurs va {$category->children_count} ta pastki kategoriya bor. Avval ularni ko'chiring yoki o'chiring!");
        }

        $category->delete();

        return back()->with('success', "Kategoriya o'chirildi!");
    }
}
