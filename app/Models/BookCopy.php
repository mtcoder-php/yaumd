<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookCopy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'book_id', 'inventory_code', 'status', 'condition_notes',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }
}
