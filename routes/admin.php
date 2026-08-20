<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\AuditLogController; // <-- 1. Controller chaqirildi

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Abituriyentlar
    Route::prefix('applicants')->name('applicants.')->group(function () {
        Route::get('/',                  [ApplicantController::class, 'index'])->name('index');
        Route::get('/{id}',              [ApplicantController::class, 'show'])->name('show');
        Route::get('/{id}/edit',         [ApplicantController::class, 'edit'])->name('edit');
        Route::put('/{id}',              [ApplicantController::class, 'update'])->name('update');
        Route::patch('/{id}/status',     [ApplicantController::class, 'updateStatus'])->name('status');
    });

    // Audit loglar (Tizim harakatlari jurnali)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index'); // <-- 2. Route qo'shildi

});
