<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TurnstileEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HR uchun — xodimlarning turniketdan o'tish (kirish/chiqish) tarixi,
 * sana/xodim/yo'nalish bo'yicha filtrlab ko'riladigan jadval.
 *
 * MUHIM: bu — 'attendance:notify-staff' (kun oxirida kech qolish/erta
 * ketish xabari) va 'turnstile:notify-staff' (har voqeada darhol xabar)
 * BILAN BOG'LIQ, lekin ular Telegram xabar yuboradi, bu esa faqat
 * admin panelda KO'RISH uchun — real vaqtli hisobot HAR IKKALASI ham
 * (Telegram + bu sahifa) birga ishlaydi.
 */
class StaffAttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $date = $request->input('date') ?: now()->toDateString();
        $direction = $request->input('direction') ?: null;
        $search = trim((string) $request->input('q', ''));

        $staffIds = null;

        if ($search !== '') {
            $staffIds = User::where('full_name', 'like', "%{$search}%")->pluck('id');
        }

        $base = TurnstileEvent::query()
            ->where('matched_type', 'staff')
            ->whereNotNull('matched_id')
            ->where('major', TurnstileEvent::MAJOR_ACCESS_CONTROL)
            ->where('minor', TurnstileEvent::MINOR_FACE_RECOGNIZED)
            ->whereDate('event_time', $date)
            ->when($staffIds !== null, fn ($q) => $q->whereIn('matched_id', $staffIds))
            ->when($direction, fn ($q) => $q->whereHas('device', fn ($q2) => $q2->where('direction', $direction)));

        $uniqueStaffCount = (clone $base)->distinct('matched_id')->count('matched_id');
        $totalCount = (clone $base)->count();

        $events = (clone $base)
            ->with('device')
            ->orderByDesc('event_time')
            ->paginate(30)
            ->withQueryString();

        $userIds = collect($events->items())->pluck('matched_id')->unique();
        $users = User::whereIn('id', $userIds)->get(['id', 'full_name'])->keyBy('id');

        $events->getCollection()->transform(fn (TurnstileEvent $event) => [
            'id'          => $event->id,
            'date'        => $event->event_time->format('Y-m-d'),
            'time'        => $event->event_time->format('H:i'),
            'direction'   => $event->device?->direction,
            'device_name' => $event->device?->name,
            'staff_name'  => $users->get($event->matched_id)?->full_name ?? "Noma'lum",
        ]);

        return Inertia::render('Admin/Attendance/Index', [
            'events' => $events,
            'stats'  => [
                'total_events' => $totalCount,
                'staff_count'  => $uniqueStaffCount,
            ],
            'filters' => [
                'date'      => $date,
                'direction' => $direction,
                'q'         => $search,
            ],
        ]);
    }
}
