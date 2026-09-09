<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestEmailChangeRequest;
use App\Http\Requests\UpdateProfilePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Notifications\EmailChangeCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Har qanday rol (super-admin, admin, o'qituvchi, talaba va h.k.) o'zining
 * shaxsiy profilini shu yerdan boshqaradi — routes/admin.php'da 'auth'dan
 * boshqa hech qanday `permission:` middleware talab qilinmaydi, chunki bu
 * o'zining hisobi ustidan amal, boshqa foydalanuvchilarniki emas
 * (boshqalarni boshqarish uchun UserController mavjud).
 */
class ProfileController extends Controller
{
    private const CODE_TTL_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_VERIFY_ATTEMPTS = 5;

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $pending = Cache::get($this->emailChangeCacheKey($user));

        return Inertia::render('Admin/Profile/Index', [
            'user' => [
                'id'         => $user->id,
                'full_name'  => $user->full_name,
                'phone'      => $user->phone,
                'address'    => $user->address,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
                'gender'     => $user->gender,
                'email'      => $user->email,
                'photo_url'  => $user->photo_url,
            ],
            'pendingEmailChange' => $pending
                ? ['email' => $this->maskEmail($pending['email'])]
                : null,
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $request->user()->update($request->validated());

        return back()->with('success', "Ma'lumotlar yangilandi!");
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'photo.required' => 'Rasmni tanlang',
            'photo.image'    => 'Fayl rasm bo\'lishi kerak',
            'photo.mimes'    => 'Faqat jpg, png yoki webp formatlar qabul qilinadi',
            'photo.max'      => 'Rasm hajmi 2 MB dan oshmasligi kerak',
        ]);

        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update([
            'photo' => $request->file('photo')->store('avatars', 'public'),
        ]);

        return back()->with('success', 'Profil rasmi yangilandi!');
    }

    public function destroyPhoto(Request $request)
    {
        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->update(['photo' => null]);
        }

        return back()->with('success', 'Profil rasmi o\'chirildi!');
    }

    public function updatePassword(UpdateProfilePasswordRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Parol muvaffaqiyatli yangilandi!');
    }

    /**
     * Yangi emailga tasdiqlash kodi yuboradi — hisob email'i faqat
     * kod tasdiqlangandan keyin (verifyEmailChange) o'zgaradi, chunki
     * tizimga kirish kodi (AuthController) ham shu emailga bog'liq.
     */
    public function requestEmailChange(RequestEmailChangeRequest $request)
    {
        $user = $request->user();
        $resendKey = "profile-email-resend.{$user->id}";

        if (RateLimiter::tooManyAttempts($resendKey, 1)) {
            $seconds = RateLimiter::availableIn($resendKey);

            return back()->withErrors([
                'new_email' => "Yangi kod so'rashdan oldin {$seconds} soniya kuting",
            ]);
        }

        RateLimiter::hit($resendKey, self::RESEND_COOLDOWN_SECONDS);

        $code = (string) random_int(100000, 999999);

        Cache::put($this->emailChangeCacheKey($user), [
            'email' => $request->new_email,
            'hash'  => Hash::make($code),
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        // MUHIM: $user->notify() ishlatilmadi — u hisobning ESKI emailiga
        // yuboradi. Bu yerda esa hali bazaga yozilmagan YANGI manzilni
        // tekshirish kerak, shuning uchun on-demand notification.
        Notification::route('mail', $request->new_email)
            ->notify(new EmailChangeCodeNotification($code, $user->full_name, self::CODE_TTL_MINUTES));

        return back()->with('success', 'Tasdiqlash kodi yangi emailga yuborildi.');
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate(['code' => 'required|string'], [
            'code.required' => 'Kodni kiriting',
        ]);

        $user = $request->user();
        $verifyKey = "profile-email-verify.{$user->id}";

        if (RateLimiter::tooManyAttempts($verifyKey, self::MAX_VERIFY_ATTEMPTS)) {
            Cache::forget($this->emailChangeCacheKey($user));
            RateLimiter::clear($verifyKey);

            return back()->withErrors([
                'code' => "Juda ko'p noto'g'ri urinish. Emailni qaytadan o'zgartirishni so'rang.",
            ]);
        }

        $cached = Cache::get($this->emailChangeCacheKey($user));

        if (! $cached || ! Hash::check($request->code, $cached['hash'])) {
            RateLimiter::hit($verifyKey, 600); // 10 daqiqa

            return back()->withErrors([
                'code' => "Kod noto'g'ri yoki muddati o'tgan",
            ]);
        }

        RateLimiter::clear($verifyKey);
        Cache::forget($this->emailChangeCacheKey($user));

        $user->update(['email' => $cached['email']]);

        return back()->with('success', 'Email manzil muvaffaqiyatli yangilandi!');
    }

    public function cancelEmailChange(Request $request)
    {
        Cache::forget($this->emailChangeCacheKey($request->user()));

        return back()->with('success', 'Email o\'zgartirish bekor qilindi.');
    }

    private function emailChangeCacheKey(User $user): string
    {
        return "profile-email-change.{$user->id}";
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
