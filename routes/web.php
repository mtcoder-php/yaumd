<?php

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\AdmissionController;
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

// ─── AUTH ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
});
