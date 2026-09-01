<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            Route::middleware('web')
                ->group(base_path('routes/cabinet.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Spatie Laravel Permission — Laravel 12'ning yangi bootstrap
        // tuzilishida (app/Http/Kernel.php yo'q) bu middleware'lar avtomatik
        // ro'yxatdan o'tmaydi, shuning uchun qo'lda alias qilinadi.
        $middleware->alias([
            'role'               => RoleMiddleware::class,
            'permission'         => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // xAPI (Tin Can) paketlari ichidagi kutubxona statement'larni
        // to'g'ridan-to'g'ri o'zining HTTP so'rovi bilan yuboradi — u
        // Laravel'ning CSRF tokenini bilmaydi va uni yubora olmaydi.
        // Autentifikatsiya baribir sessiya cookie orqali ta'minlanadi
        // (iframe bizning o'z domenimizdan ochiladi), shuning uchun bu
        // xavfsizlikni pasaytirmaydi — faqat shu bitta marshrut turkumi
        // uchun CSRF tekshiruvi o'chiriladi.
        $middleware->validateCsrfTokens(except: [
            'admin/my-courses/*/lessons/*/xapi/statements',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
