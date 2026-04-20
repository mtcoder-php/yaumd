<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required'    => 'Email majburiy',
            'email.email'       => 'Email noto\'g\'ri formatda',
            'password.required' => 'Parol majburiy',
            'password.min'      => 'Parol kamida 6 ta belgi bo\'lishi kerak',
        ]);

        $key = 'login.' . Str::lower($request->email) . '.' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Juda ko'p urinish. {$seconds} soniyadan so'ng qayta urinib ko'ring.",
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 900); // 15 daqiqa
            return back()->withErrors([
                'email' => 'Email yoki parol noto\'g\'ri',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        // Rolga qarab yo'naltirish
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
