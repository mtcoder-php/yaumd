<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Setting;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UniversityController extends Controller
{
    /**
     * "Universitet haqida" > "Umumiy ma'lumot" — /universitet/haqida/umumiy
     */
    public function about(): Response
    {
        return Inertia::render('Web/University/About', [
            'settings' => $this->settings(),
        ]);
    }

    /**
     * "Professor & o'qituvchilar" — /university/about/staff. Qidiruv
     * (ism-familiya), Fakultet/Kafedra/Ilmiy daraja bo'yicha filtr va
     * sahifalash bilan — hammasi haqiqiy 'staff' jadvalidan (type=teacher).
     */
    public function staff(Request $request): Response
    {
        $query = Staff::query()
            ->where('type', 'teacher')
            ->where('is_active', true)
            ->with(['faculty:id,name_uz,short_name', 'department:id,name_uz']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('full_name_uz', 'like', "%{$search}%");
        }

        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->integer('faculty_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        if ($request->filled('degree')) {
            $query->where('degree', $request->string('degree'));
        }

        match ($request->string('sort', 'name_asc')->toString()) {
            'name_desc' => $query->orderByDesc('full_name_uz'),
            default     => $query->orderBy('full_name_uz'),
        };

        // MUHIM: statistika kartalari (120+, 18, 45, 57) qidiruv/filtrga
        // qaramay har doim UMUMIY sonlarni ko'rsatadi — namunadagi kabi
        // sahifa tepasidagi doimiy ko'rsatkichlar, filtrlangan ro'yxat soni
        // emas.
        return Inertia::render('Web/University/Staff', [
            'staff'      => $query->paginate(8)->withQueryString(),
            'filters'    => $request->only(['search', 'faculty_id', 'department_id', 'degree', 'sort']),
            'stats'      => [
                'total'     => Staff::where('type', 'teacher')->where('is_active', true)->count(),
                'professor' => Staff::where('type', 'teacher')->where('is_active', true)->where('degree', 'professor')->count(),
                'dotsent'   => Staff::where('type', 'teacher')->where('is_active', true)->where('degree', 'dotsent')->count(),
                'phd'       => Staff::where('type', 'teacher')->where('is_active', true)->where('degree', 'phd')->count(),
            ],
            // MUHIM: barcha 5 ta rahbariyat a'zosi ko'rsatiladi (take() bilan
            // cheklanmaydi) — hozircha aniq shu son (rektor + 4 prorektor).
            'leadership'  => Staff::where('type', 'leadership')->where('is_active', true)
                ->orderBy('order')->get(),
            'faculties'   => Faculty::where('is_active', true)->get(),
            // MUHIM: "Kafedralar" bo'limida har bir kafedraning yo'nalishlar
            // sonini ko'rsatish uchun 'directions_count' ham qo'shildi.
            'departments' => Department::where('is_active', true)->withCount('directions')->orderBy('name_uz')->get(),
            'settings'    => $this->settings(),
        ]);
    }

    /**
     * Bitta professor/o'qituvchining profil sahifasi —
     * /university/about/staff/{staff}. Hozircha faqat "Umumiy ma'lumot"
     * tabi to'liq: bio, statistika, Ta'lim va malaka + Ish tajribasi vaqt
     * chiziqlari, ijtimoiy tarmoqlar va shu kafedradagi hamkasblar.
     */
    public function staffShow(Staff $staff): Response
    {
        abort_unless($staff->is_active, 404);

        $staff->load([
            'faculty:id,name_uz,short_name',
            'department:id,name_uz',
            'educations',
            'experiences',
            'socialLinks',
            'articles',
            'projects',
        ]);

        return Inertia::render('Web/University/StaffProfile', [
            'staff'      => $staff,
            // Hamkasblar — bitta hodisa: hozircha bo'lim (department) mos
            // kelsa yetarli, chunki bizda ko'pchilik kafedralar bitta
            // fakultetga tegishli.
            'colleagues' => Staff::where('type', 'teacher')
                ->where('is_active', true)
                ->where('department_id', $staff->department_id)
                ->where('id', '!=', $staff->id)
                ->with('department:id,name_uz')
                ->limit(4)
                ->get(['id', 'full_name_uz', 'position_uz', 'photo', 'department_id']),
            'settings'   => $this->settings(),
        ]);
    }

    private function settings(): array
    {
        return [
            'phone'       => Setting::get('phone'),
            'email'       => Setting::get('email'),
            'address'     => Setting::get('address'),
            'description' => Setting::get('description'),
        ];
    }
}
