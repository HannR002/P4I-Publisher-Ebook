<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureAuthorIsVerified;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAuthor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/midtrans/webhook',
            'checkout/webhook', // in case we put it in web.php
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'author' => EnsureUserIsAuthor::class,
            'author.verified' => EnsureAuthorIsVerified::class,
            'feature' => EnsureFeatureEnabled::class,
        ]);

        $middleware->web(append: [
            EnsureAccountIsActive::class,
            AuthenticateSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
