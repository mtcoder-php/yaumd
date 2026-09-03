<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class LibraryBook extends Model
{
    use SoftDeletes;

    protected $appends = ['cover_image_url'];

    protected $fillable = [
        'category_id', 'isbn', 'title', 'author',
        'publisher', 'published_year', 'language',
        'description', 'cover_image', 'file_path',
        'page_count', 'shelf_location', 'added_by',
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

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(LibraryAccess::class, 'book_id');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'book_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
