<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Talabalar (va boshqa har qanday login qilgan foydalanuvchi) uchun
// kutubxona katalogini FAQAT KO'RISH sahifasi. "Kurslarim"dagi
// StudentCourseController bilan bir xil naqsh: alohida, mustaqil
// controller — routes/admin.php'da 'permission:' talab qilinmaydi, faqat
// 'auth' yetarli, chunki bu yerda hech qanday CRUD yo'q, faqat faol
// kitoblarni ko'rish.
class StudentLibraryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = LibraryBook::with('category')
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($q) => $q->where('status', 'available'),
            ])
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            })
            ->orderBy('title');

        return Inertia::render('Student/Library/Index', [
            'books'      => $query->paginate(24)->withQueryString(),
            'categories' => LibraryCategory::where('is_active', true)->orderBy('name_uz')->get(['id', 'name_uz']),
            'filters'    => $request->only(['category_id', 'search']),
        ]);
    }

    public function show(int $id): Response
    {
        $book = LibraryBook::with('category')
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($q) => $q->where('status', 'available'),
            ])
            ->where('is_active', true)
            ->findOrFail($id);

        return Inertia::render('Student/Library/Show', [
            'book' => $book,
        ]);
    }
}
