<?php

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\AdmissionController;
use App\Http\Controllers\Web\CertificateVerifyController;
use App\Http\Controllers\Web\ClickCallbackController;
use App\Http\Controllers\Web\PaymeCallbackController;
use App\Http\Controllers\Web\PublicPaymentReturnController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// ─── OMMAVIY SAYT ────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// Qabul
Route::prefix('qabul')->name('qabul.')->group(function () {
    Route::get('/ariza', [AdmissionController::class, 'create'])->name('ariza');
    Route::post('/ariza', [AdmissionController::class, 'store'])->name('ariza.store');
    Route::get('/ariza/muvaffaqiyat', [AdmissionController::class, 'success'])->name('ariza.success');
});

// Sertifikatni tekshirish — sertifikat PDF'idagi QR kod orqali ochiladi,
// login talab qilinmaydi (istalgan kishi, masalan ish beruvchi, sertifikat
// raqamini kiritib uning haqiqiyligini tekshirishi mumkin).
Route::get('/certificates/{number}', [CertificateVerifyController::class, 'show'])->name('certificates.verify');

// Pullik elektron kitoblar uchun to'lov tizimlari callback'lari — Click.uz
// va Payme serverlaridan to'g'ridan-to'g'ri keladi, login talab qilinmaydi,
// CSRF tekshiruvidan ham ozod qilingan (bootstrap/app.php'ga qarang).
Route::post('/payments/click/callback', [ClickCallbackController::class, 'callback'])->name('payments.click.callback');
Route::post('/payments/payme/callback', [PaymeCallbackController::class, 'callback'])->name('payments.payme.callback');

// Kassir /admin/payments sahifasida shartnoma uchun Click/Payme havolasi
// (QR) generatsiya qilganda, to'lovchi (talaba yoki uning yaqini) o'z
// qurilmasida to'lab bo'lgach shu manzilga qaytariladi — login talab
// qilinmaydi, chunki to'lovchi tizimda umuman hisobga ega bo'lmasligi
// mumkin (PublicPaymentReturnController'ga qarang).
Route::get('/pay/{payment}/return', [PublicPaymentReturnController::class, 'contract'])->name('payments.public.return');

// ─── AUTH ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // 2 bosqichli kirish: parol to'g'ri bo'lgach, emailga yuborilgan kod tasdiqlanadi
    Route::get('/login/verify',        [AuthController::class, 'showVerify'])->name('login.verify');
    Route::post('/login/verify',       [AuthController::class, 'verify'])->name('login.verify.post');
    Route::post('/login/verify/resend', [AuthController::class, 'resend'])->name('login.verify.resend');
    Route::post('/login/verify/cancel', [AuthController::class, 'cancelVerify'])->name('login.verify.cancel');

    // Parolni unutdim
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
});
