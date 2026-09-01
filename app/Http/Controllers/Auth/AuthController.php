<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LoginCodeNotification;
use App\Services\AuditService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    private const CODE_TTL_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_VERIFY_ATTEMPTS = 5;

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

        // Auth::validate — parolni tekshiradi, lekin hali tizimga kirgizmaydi
        // (bosqich 2: emailga yuborilgan kod tasdiqlanmaguncha sessiya ochilmaydi)
        if (! Auth::validate($request->only('email', 'password'))) {
            RateLimiter::hit($key, 900); // 15 daqiqa
            return back()->withErrors([
                'email' => 'Email yoki parol noto\'g\'ri',
            ]);
        }

        RateLimiter::clear($key);

        $user = User::where('email', $request->email)->first();

        if (! $user->is_active) {
            return back()->withErrors([
                'email' => "Hisobingiz faol emas. Administratorga murojaat qiling.",
            ]);
        }

        $this->issueAndSendCode($user);

        $request->session()->put('2fa.user_id', $user->id);
        $request->session()->put('2fa.remember', $request->boolean('remember'));

        return redirect()->route('login.verify');
    }

    public function showVerify(Request $request)
    {
        $userId = $request->session()->get('2fa.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        return Inertia::render('Auth/VerifyCode', [
            'maskedEmail' => $user ? $this->maskEmail($user->email) : null,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Kodni kiriting',
        ]);

        $userId = $request->session()->get('2fa.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $verifyKey = "login-verify.{$userId}";

        if (RateLimiter::tooManyAttempts($verifyKey, self::MAX_VERIFY_ATTEMPTS)) {
            $this->resetPending($request);
            return redirect()->route('login')->withErrors([
                'email' => "Juda ko'p noto'g'ri urinish. Iltimos, qaytadan kiring.",
            ]);
        }

        $cacheKey = "login-code.{$userId}";
        $cached = Cache::get($cacheKey);

        if (! $cached || ! Hash::check($request->code, $cached['hash'])) {
            RateLimiter::hit($verifyKey, 600); // 10 daqiqa
            return back()->withErrors([
                'code' => "Kod noto'g'ri yoki muddati o'tgan",
            ]);
        }

        RateLimiter::clear($verifyKey);
        Cache::forget($cacheKey);

        $user = User::findOrFail($userId);
        $remember = (bool) $request->session()->get('2fa.remember');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->forget(['2fa.user_id', '2fa.remember']);

        AuditService::log('login');
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resend(Request $request)
    {
        $userId = $request->session()->get('2fa.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $resendKey = "login-code-resend.{$userId}";

        if (RateLimiter::tooManyAttempts($resendKey, 1)) {
            $seconds = RateLimiter::availableIn($resendKey);
            return back()->withErrors([
                'code' => "Yangi kod so'rashdan oldin {$seconds} soniya kuting",
            ]);
        }

        RateLimiter::hit($resendKey, self::RESEND_COOLDOWN_SECONDS);

        $user = User::findOrFail($userId);
        $this->issueAndSendCode($user);

        return back()->with('success', 'Yangi kod yuborildi!');
    }

    public function cancelVerify(Request $request)
    {
        $this->resetPending($request);

        return redirect()->route('login');
    }

    /**
     * "Parolni unutdingizmi?" — email manzilga tiklash havolasi yuborish.
     * Laravel'ning tayyor Password Reset Broker'idan foydalanamiz
     * (password_reset_tokens jadvali), xat matni AppServiceProvider'da
     * o'zbekcha qilib moslashtirilgan (ResetPassword::toMailUsing).
     */
    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email kiriting',
            'email.email'    => 'Email noto\'g\'ri formatda',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => "Iltimos, yangi havola so'rashdan oldin biroz kuting.",
            ]);
        }

        // Email tizimda mavjud yoki yo'qligini bildirmaymiz — xavfsizlik
        // nuqtai nazaridan bir xil xabar qaytariladi (status'dan qat'i nazar).
        return back()->with('success', "Agar bu email tizimda ro'yxatdan o'tgan bo'lsa, unga parolni tiklash havolasi yuborildi.");
    }

    public function showResetPassword(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required'     => 'Email majburiy',
            'password.required'  => 'Parol majburiy',
            'password.min'       => 'Parol kamida 6 ta belgi bo\'lishi kerak',
            'password.confirmed' => 'Parollar mos kelmadi',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Parolingiz muvaffaqiyatli yangilandi! Endi yangi parolingiz bilan tizimga kiring.');
        }

        return back()->withErrors([
            'email' => $this->passwordResetErrorMessage($status),
        ]);
    }

    private function passwordResetErrorMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => "Havola noto'g'ri yoki muddati o'tgan. Qaytadan so'rang.",
            Password::INVALID_USER  => 'Bunday email topilmadi.',
            default                 => "Xatolik yuz berdi. Qaytadan urinib ko'ring.",
        };
    }

    public function logout(Request $request)
    {
        // Audit log yozish (Logout - Auth::logout'dan oldin yoziladi, chunki user_id kerak)
        AuditService::log('logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function issueAndSendCode(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        Cache::put("login-code.{$user->id}", [
            'hash' => Hash::make($code),
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        $user->notify(new LoginCodeNotification($code, self::CODE_TTL_MINUTES));
    }

    private function resetPending(Request $request): void
    {
        $userId = $request->session()->get('2fa.user_id');

        if ($userId) {
            Cache::forget("login-code.{$userId}");
        }

        $request->session()->forget(['2fa.user_id', '2fa.remember']);
    }

    private function maskEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        $visible = Str::substr($name, 0, min(2, strlen($name)));

        return $visible . str_repeat('*', max(strlen($name) - strlen($visible), 3)) . '@' . $domain;
    }
}
