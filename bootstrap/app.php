<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/midtrans/webhook',
            'checkout/webhook' // in case we put it in web.php
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'author' => \App\Http\Middleware\EnsureUserIsAuthor::class,
            'author.verified' => \App\Http\Middleware\EnsureAuthorIsVerified::class,
            'feature' => \App\Http\Middleware\EnsureFeatureEnabled::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
