<?php

use App\Http\Middleware\AdminAuthenticated;
use App\Http\Middleware\AdminIpAllowed;
use App\Http\Middleware\ImapAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin routes are always registered; actual access is controlled by
            // the AdminAuthenticated middleware and the MAIL_ADMIN env flag.
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'imap.auth'     => ImapAuthenticated::class,
            'admin.auth'    => AdminAuthenticated::class,
            'admin.ip'      => AdminIpAllowed::class,
        ]);
        // Server-to-server endpoints called without a browser CSRF token.
        $middleware->validateCsrfTokens(except: [
            '/sso/issue',
            '/api/sync/account',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
