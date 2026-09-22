<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffSocialLink extends Model
{
    use HasFactory;

    // MUHIM: StaffEducation'dagi kabi noaniqlikni oldini olish uchun
    // jadval nomi aniq ko'rsatildi (migratsiyaga qarang: staff_social_links).
    protected $table = 'staff_social_links';

    protected $fillable = ['staff_id', 'platform', 'label', 'url', 'sort_order'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
