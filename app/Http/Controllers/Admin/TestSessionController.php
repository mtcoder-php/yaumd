<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TestSession::with(['applicant', 'direction.faculty'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('applicant', function ($q) use ($search) {
                $q->where('passport_series', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/TestSessions/Index', [
            'sessions' => $query->paginate(20)->withQueryString(),
            'filters'  => $request->only(['status', 'search']),
        ]);
    }

    public function destroy(int $id)
    {
        TestSession::findOrFail($id)->delete();
        return back()->with('success', "Test sessiyasi o'chirildi!");
    }



    public function reset(int $id)
    {
        $session   = TestSession::findOrFail($id);
        $applicant = $session->applicant;

        // Eski sessiyani o'chirish
        $session->delete();

        // Yangi sessiya yaratish
        $password = sprintf(
            '%02d.%02d.%d',
            $applicant->birth_day,
            $applicant->birth_month,
            $applicant->birth_year
        );

        TestSession::create([
            'applicant_id'    => $applicant->id,
            'direction_id'    => $applicant->direction_id,
            'language'        => 'uz',
            'foreign_lang'    => 'en',
            'login'           => $applicant->passport_series,
            'password_plain'  => $password,
            'password'        => bcrypt($password),
            'status'          => 'pending',
            'expires_at'      => now()->addDays(30),
            'total_questions' => 90,
        ]);

        return back()->with('success', 'Test sessiyasi yangilandi!');
    }
}
