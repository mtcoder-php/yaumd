<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TestQuestionController;
use App\Http\Controllers\Admin\DirectionSubjectController;
use App\Http\Controllers\Admin\TestSessionController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\DirectionController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\InterviewController;


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Abituriyentlar
    Route::prefix('applicants')->name('applicants.')->group(function () {
        Route::get('/', [ApplicantController::class, 'index'])->name('index');
        Route::get('/{id}', [ApplicantController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ApplicantController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ApplicantController::class, 'update'])->name('update');
        Route::patch('/{id}/status', [ApplicantController::class, 'updateStatus'])->name('status');
        Route::patch('/bulk-status', [ApplicantController::class, 'bulkUpdateStatus'])->name('bulk-status');
    });

    // Audit log
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Fanlar va savollar
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/create', [SubjectController::class, 'create'])->name('create');
        Route::post('/', [SubjectController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SubjectController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SubjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [SubjectController::class, 'destroy'])->name('destroy');

        // Savollar — fan ichida
        Route::get('/{id}/questions', [TestQuestionController::class, 'index'])->name('questions.index');
        Route::get('/{id}/questions/create', [TestQuestionController::class, 'create'])->name('questions.create');
        Route::post('/{id}/questions', [TestQuestionController::class, 'store'])->name('questions.store');
        Route::get('/{id}/questions/template', [TestQuestionController::class, 'template'])->name('questions.template');
        Route::post('/{id}/questions/import', [TestQuestionController::class, 'import'])->name('questions.import');
        Route::get('/{id}/questions/{qId}/edit', [TestQuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/{id}/questions/{qId}', [TestQuestionController::class, 'update'])->name('questions.update');
        Route::delete('/{id}/questions/{qId}', [TestQuestionController::class, 'destroy'])->name('questions.destroy');
    });

    // Yo'nalish-fanlar
    Route::prefix('direction-subjects')->name('direction-subjects.')->group(function () {
        Route::get('/', [DirectionSubjectController::class, 'index'])->name('index');
        Route::post('/', [DirectionSubjectController::class, 'store'])->name('store');
        Route::put('/{id}', [DirectionSubjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [DirectionSubjectController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('test-sessions')->name('test-sessions.')->group(function () {
        Route::get('/', [TestSessionController::class, 'index'])->name('index');
        Route::post('/{id}/reset', [TestSessionController::class, 'reset'])->name('reset');
        Route::delete('/{id}', [TestSessionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/',          [ContractController::class, 'index'])->name('index');
        Route::get('/create',    [ContractController::class, 'create'])->name('create');
        Route::post('/',         [ContractController::class, 'store'])->name('store');
        Route::get('/{id}',      [ContractController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [ContractController::class, 'update'])->name('update');
        Route::get('/{id}/pdf',  [ContractController::class, 'generatePdf'])->name('pdf'); // ← qo'shing
        Route::delete('/{id}',   [ContractController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',          [UserController::class, 'index'])->name('index');
        Route::get('/create',    [UserController::class, 'create'])->name('create');
        Route::post('/',         [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [UserController::class, 'update'])->name('update');
        Route::delete('/{id}',   [UserController::class, 'destroy'])->name('destroy');
    });



    Route::prefix('faculties')->name('faculties.')->group(function () {
        Route::get('/',          [FacultyController::class, 'index'])->name('index');
        Route::get('/create',    [FacultyController::class, 'create'])->name('create');
        Route::post('/',         [FacultyController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [FacultyController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [FacultyController::class, 'update'])->name('update');
        Route::delete('/{id}',   [FacultyController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('directions')->name('directions.')->group(function () {
        Route::get('/',          [DirectionController::class, 'index'])->name('index');
        Route::get('/create',    [DirectionController::class, 'create'])->name('create');
        Route::post('/',         [DirectionController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [DirectionController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [DirectionController::class, 'update'])->name('update');
        Route::delete('/{id}',   [DirectionController::class, 'destroy'])->name('destroy');
    });


    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/',          [DepartmentController::class, 'index'])->name('index');
        Route::get('/create',    [DepartmentController::class, 'create'])->name('create');
        Route::post('/',         [DepartmentController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{id}',   [DepartmentController::class, 'destroy'])->name('destroy');
    });


    Route::prefix('interviews')->name('interviews.')->group(function () {
        Route::get('/',      [InterviewController::class, 'index'])->name('index');
        Route::post('/',     [InterviewController::class, 'store'])->name('store');
    });
});
