<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookCopyRequest;
use App\Http\Requests\UpdateBookCopyRequest;
use App\Models\BookCopy;
use App\Models\LibraryBook;

class BookCopyController extends Controller
{
    public function store(StoreBookCopyRequest $request, int $id)
    {
        $book = LibraryBook::findOrFail($id);

        $book->copies()->create($request->validated());

        return back()->with('success', "Nusxa qo'shildi!");
    }

    public function update(UpdateBookCopyRequest $request, int $id, int $copyId)
    {
        $copy = BookCopy::where('book_id', $id)->findOrFail($copyId);

        $copy->update($request->validated());

        return back()->with('success', 'Nusxa ma\'lumotlari yangilandi!');
    }

    public function destroy(int $id, int $copyId)
    {
        $copy = BookCopy::where('book_id', $id)->findOrFail($copyId);

        if ($copy->status === 'loaned') {
            return back()->with('error', "Bu nusxa hozir talaba qo'lida — avval qaytarilishi kerak.");
        }

        $copy->delete();

        return back()->with('success', "Nusxa o'chirildi!");
    }
}
