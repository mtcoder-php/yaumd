<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsArticle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'slug',
        'title_uz', 'title_ru', 'title_en',
        'excerpt_uz', 'excerpt_ru', 'excerpt_en',
        'content_uz', 'content_ru', 'content_en',
        'image', 'views', 'is_published', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
