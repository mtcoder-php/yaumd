<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryCategory extends Model
{
    protected $fillable = ['name_uz', 'name_ru', 'name_en', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function books(): HasMany
    {
        return $this->hasMany(LibraryBook::class, 'category_id');
    }
}
