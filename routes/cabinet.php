<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cabinet\CabinetAuthController;
use App\Http\Controllers\Cabinet\TestController;

Route::prefix('cabinet')->name('cabinet.')->group(function () {

    // Auth
    Route::get('/login',   [CabinetAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [CabinetAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [CabinetAuthController::class, 'logout'])->name('logout');

    // Test
    Route::get('/test/language',  [TestController::class, 'language'])->name('test.language');
    Route::post('/test/language', [TestController::class, 'setLanguage'])->name('test.language.set');
    Route::get('/test/start',     [TestController::class, 'start'])->name('test.start');
    Route::post('/test/answer',   [TestController::class, 'saveAnswer'])->name('test.answer');
    Route::post('/test/finish',   [TestController::class, 'finish'])->name('test.finish');
    Route::get('/test/result',    [TestController::class, 'result'])->name('test.result');

});
