<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ApplicantController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Abituriyentlar
    Route::prefix('applicants')->name('applicants.')->group(function () {
        Route::get('/',              [ApplicantController::class, 'index'])->name('index');
        Route::get('/{id}',          [ApplicantController::class, 'show'])->name('show');
        Route::patch('/{id}/status', [ApplicantController::class, 'updateStatus'])->name('status');
    });

});
