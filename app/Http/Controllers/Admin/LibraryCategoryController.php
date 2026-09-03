<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLibraryCategoryRequest;
use App\Http\Requests\UpdateLibraryCategoryRequest;
use App\Models\LibraryCategory;
use Inertia\Inertia;
use Inertia\Response;

class LibraryCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/LibraryCategories/Index', [
            'categories' => LibraryCategory::withCount('books')
                ->orderBy('name_uz')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/LibraryCategories/Create');
    }

    public function store(StoreLibraryCategoryRequest $request)
    {
        LibraryCategory::create($request->validated());

        return redirect()->route('admin.library-categories.index')
            ->with('success', 'Kategoriya yaratildi!');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/LibraryCategories/Edit', [
            'category' => LibraryCategory::findOrFail($id),
        ]);
    }

    public function update(UpdateLibraryCategoryRequest $request, int $id)
    {
        LibraryCategory::findOrFail($id)->update($request->validated());

        return redirect()->route('admin.library-categories.index')
            ->with('success', 'Kategoriya yangilandi!');
    }

    public function destroy(int $id)
    {
        $category = LibraryCategory::withCount('books')->findOrFail($id);

        if ($category->books_count > 0) {
            return back()->with('error', "Bu kategoriyada {$category->books_count} ta kitob bor. Avval ularni boshqa kategoriyaga o'tkazing yoki o'chiring!");
        }

        $category->delete();

        return back()->with('success', "Kategoriya o'chirildi!");
    }
}
