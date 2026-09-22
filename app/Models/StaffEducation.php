<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffEducation extends Model
{
    use HasFactory;

    /**
     * MUHIM: Laravel'ning avtomatik jadval nomi taxmin qilish mexanizmi
     * "Education" so'zini sanalmaydigan (uncountable) so'z deb hisoblaydi
     * va jamlik qo'shmaydi — shuning uchun standart holatda "staff_education"
     * (ko'plik "s"siz) deb taxmin qilinardi, migratsiyadagi haqiqiy jadval
     * nomi esa "staff_educations". Noaniqlikni butunlay yo'q qilish uchun
     * jadval nomi aniq ko'rsatildi.
     */
    protected $table = 'staff_educations';

    protected $fillable = ['staff_id', 'period', 'title', 'subtitle', 'sort_order'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
