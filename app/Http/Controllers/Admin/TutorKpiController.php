<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TutorKpiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tutor KPI — CRM hisoboti (Moliya/Super Admin uchun barcha tutorlar
 * ro'yxati va har birining tafsiloti) va tutorning o'zi uchun "Mening
 * KPI'm" shaxsiy sahifasi. Hisob-kitobning o'zi butunlay
 * TutorKpiService'da — bu kontroller faqat kirish huquqini tekshiradi va
 * natijani Inertia orqali uzatadi (mantiq ikki joyda takrorlanmasin uchun).
 */
class TutorKpiController extends Controller
{
    public function __construct(private TutorKpiService $service)
    {
    }

    /**
     * Barcha tutorlar bo'yicha qisqa ro'yxat — 'crm.view' permissioniga ega
     * xodimlar uchun (admin/moliya/super-admin).
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Crm/TutorKpi/Index', [
            'tutors' => $this->service->summaryForAllTutors(),
        ]);
    }

    /**
     * Bitta tutor bo'yicha to'liq tafsilot — xodim (crm.view) shu yerdan
     * ko'radi. Sahifaning o'zi ("Show.vue") tutorning o'z-o'ziga
     * ko'radigan "Mening KPI'm" sahifasi bilan bitta — pastdagi myKpi()ga
     * qarang.
     */
    public function show(int $id): Response
    {
        $tutor = User::role('tutor')->findOrFail($id);

        return Inertia::render('Admin/Crm/TutorKpi/Show', [
            'report' => $this->service->reportForTutor($tutor),
            'isSelf' => false,
        ]);
    }

    /**
     * "Mening KPI'm" — tutorning o'zi shaxsiy kabinetidan o'z
     * guruhlari/talabalarining shu oy uchun to'lov ko'rsatkichini ko'radi.
     * boshqa "o'ziniki" sahifalar (masalan /admin/my-contract) kabi
     * hech qanday 'permission:' talab qilinmaydi — faqat login qilingan
     * va aynan 'tutor' roliga ega bo'lish yetarli, ma'lumot esa qat'iy
     * shu foydalanuvchining o'ziga (auth()->id()) cheklanadi.
     */
    public function myKpi(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user->hasRole('tutor'), 403);

        return Inertia::render('Admin/Crm/TutorKpi/Show', [
            'report' => $this->service->reportForTutor($user),
            'isSelf' => true,
        ]);
    }
}
