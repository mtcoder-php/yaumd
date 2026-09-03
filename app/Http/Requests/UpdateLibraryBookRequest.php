<?php

namespace App\Http\Requests;

class UpdateLibraryBookRequest extends StoreLibraryBookRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return array_merge(parent::rules(), [
            'isbn' => 'nullable|string|max:20|unique:library_books,isbn,' . $id,
        ]);
    }
}
