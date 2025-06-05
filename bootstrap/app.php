<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\SuperAdminAccess;
use App\Http\Middleware\AdminRecordAccess;
use App\Http\Middleware\AdminSuperAdmin;
use App\Http\Middleware\CheckAccountStatus;
use App\Http\Middleware\HandleGuestUser;
use App\Http\Middleware\EnsureInventoryStatus;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\AdminSessionValidator;
use App\Http\Middleware\RequireAuthForCart;
use App\Services\GuestCartService;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add the middleware to the web group
        $middleware->web(append: [
            HandleGuestUser::class, // Must run first to set up guest user
            CheckAccountStatus::class,
            EnsureInventoryStatus::class, // Ensure inventory status is always correct
            AdminSessionValidator::class, // Automatically log out admin when navigating away
        ]);
        
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'admin.access' => AdminAccess::class,
            'superadmin.access' => SuperAdminAccess::class,
            'admin.record.access' => AdminRecordAccess::class,
            'admin.super' => AdminSuperAdmin::class,
            'account.status' => CheckAccountStatus::class,
            'admin.check' => RedirectIfNotAdmin::class,
            'admin.auth' => AdminAuthenticate::class,
            'admin.session.validator' => AdminSessionValidator::class,
            'cart.auth' => RequireAuthForCart::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withBindings([
        // Register GuestCartService as a singleton
        GuestCartService::class => function ($app) {
            return new GuestCartService();
        },
    ])->create();
