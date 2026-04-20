<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'isbn', 'title', 'author',
        'publisher', 'published_year', 'language',
        'description', 'cover_image', 'file_path',
        'access_type', 'price',
        'download_count', 'view_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(LibraryAccess::class, 'book_id');
    }
}
