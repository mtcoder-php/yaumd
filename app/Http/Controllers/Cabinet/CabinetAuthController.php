<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class CabinetAuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Cabinet/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Pasport seriyasini kiriting',
            'password.required' => 'Parolni kiriting',
        ]);

        $session = TestSession::where('login', strtoupper(trim($request->login)))->first();

        if (!$session || !Hash::check($request->password, $session->password)) {
            return back()->withErrors([
                'login' => "Login yoki parol noto'g'ri!",
            ]);
        }

        if ($session->status === 'expired') {
            return back()->withErrors([
                'login' => 'Test sessiyangiz muddati tugagan!',
            ]);
        }

        if ($session->status === 'completed') {
            session(['cabinet_session_id' => $session->id]);
            return redirect()->route('cabinet.test.result');
        }

        if ($session->expires_at && $session->expires_at->isPast()) {
            $session->update(['status' => 'expired']);
            return back()->withErrors([
                'login' => 'Test sessiyangiz muddati tugagan!',
            ]);
        }

        session(['cabinet_session_id' => $session->id]);
        return redirect()->route('cabinet.test.language');
    }

    public function logout(Request $request)
    {
        session()->forget('cabinet_session_id');
        return redirect()->route('cabinet.login');
    }
}
