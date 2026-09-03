<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLibraryBookRequest;
use App\Http\Requests\UpdateLibraryBookRequest;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LibraryBookController extends Controller
{
    public function index(Request $request): Response
    {
        $query = LibraryBook::with('category')
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($q) => $q->where('status', 'available'),
            ])
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->latest();

        return Inertia::render('Admin/LibraryBooks/Index', [
            'books'      => $query->paginate(20)->withQueryString(),
            'categories' => LibraryCategory::orderBy('name_uz')->get(['id', 'name_uz']),
            'filters'    => $request->only(['category_id', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/LibraryBooks/Create', $this->formData());
    }

    public function store(StoreLibraryBookRequest $request)
    {
        $data = $request->validated();
        $data['added_by'] = auth()->id();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('library/covers', 'public');
        }

        $book = LibraryBook::create($data);

        return redirect()->route('admin.library.show', $book->id)
            ->with('success', "Kitob bazaga kiritildi! Endi fizik nusxalarni qo'shishingiz mumkin.");
    }

    public function show(int $id): Response
    {
        $book = LibraryBook::with(['category', 'addedBy', 'copies' => fn ($q) => $q->latest()])
            ->findOrFail($id);

        return Inertia::render('Admin/LibraryBooks/Show', [
            'book' => $book,
        ]);
    }

    public function edit(int $id): Response
    {
        $book = LibraryBook::findOrFail($id);

        return Inertia::render('Admin/LibraryBooks/Edit', array_merge(
            $this->formData(),
            ['book' => $book],
        ));
    }

    public function update(UpdateLibraryBookRequest $request, int $id)
    {
        $book = LibraryBook::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('library/covers', 'public');
        }

        $book->update($data);

        return redirect()->route('admin.library.show', $book->id)
            ->with('success', 'Kitob ma\'lumotlari yangilandi!');
    }

    public function destroy(int $id)
    {
        $book = LibraryBook::withCount('copies')->findOrFail($id);

        if ($book->copies_count > 0) {
            return back()->with('error', "Bu kitobda {$book->copies_count} ta fizik nusxa ro'yxatga olingan — o'chirib bo'lmaydi. Avval nusxalarni o'chiring.");
        }

        $book->delete();

        return back()->with('success', "Kitob o'chirildi!");
    }

    private function formData(): array
    {
        return [
            'categories' => LibraryCategory::orderBy('name_uz')->get(['id', 'name_uz']),
        ];
    }
}
