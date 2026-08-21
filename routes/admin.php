<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TestQuestionController;
use App\Http\Controllers\Admin\DirectionSubjectController;
use App\Http\Controllers\Admin\TestSessionController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Abituriyentlar
    Route::prefix('applicants')->name('applicants.')->group(function () {
        Route::get('/',              [ApplicantController::class, 'index'])->name('index');
        Route::get('/{id}',          [ApplicantController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [ApplicantController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [ApplicantController::class, 'update'])->name('update');
        Route::patch('/{id}/status', [ApplicantController::class, 'updateStatus'])->name('status');
    });

    // Audit log
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Fanlar va savollar
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/',          [SubjectController::class, 'index'])->name('index');
        Route::get('/create',    [SubjectController::class, 'create'])->name('create');
        Route::post('/',         [SubjectController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SubjectController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [SubjectController::class, 'update'])->name('update');
        Route::delete('/{id}',   [SubjectController::class, 'destroy'])->name('destroy');

        // Savollar — fan ichida
        Route::get('/{id}/questions',            [TestQuestionController::class, 'index'])->name('questions.index');
        Route::get('/{id}/questions/create',     [TestQuestionController::class, 'create'])->name('questions.create');
        Route::post('/{id}/questions',           [TestQuestionController::class, 'store'])->name('questions.store');
        Route::get('/{id}/questions/template',   [TestQuestionController::class, 'template'])->name('questions.template');
        Route::post('/{id}/questions/import',    [TestQuestionController::class, 'import'])->name('questions.import');
        Route::get('/{id}/questions/{qId}/edit', [TestQuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/{id}/questions/{qId}',      [TestQuestionController::class, 'update'])->name('questions.update');
        Route::delete('/{id}/questions/{qId}',   [TestQuestionController::class, 'destroy'])->name('questions.destroy');
    });

    // Yo'nalish-fanlar
    Route::prefix('direction-subjects')->name('direction-subjects.')->group(function () {
        Route::get('/',        [DirectionSubjectController::class, 'index'])->name('index');
        Route::post('/',       [DirectionSubjectController::class, 'store'])->name('store');
        Route::put('/{id}',    [DirectionSubjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [DirectionSubjectController::class, 'destroy'])->name('destroy');
    });




    Route::prefix('test-sessions')->name('test-sessions.')->group(function () {
        Route::get('/',        [TestSessionController::class, 'index'])->name('index');
        Route::delete('/{id}', [TestSessionController::class, 'destroy'])->name('destroy');
    });
});
