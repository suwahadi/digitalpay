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
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        
        // Add CORS middleware globally
        $middleware->web(prepend: [
            \App\Http\Middleware\HandleCors::class,
        ]);
        
        // Register custom middleware
        $middleware->alias([
            'member' => \App\Http\Middleware\MemberOnly::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'midtrans/callback',
            'midtrans/*',
            'api/midtrans/notification',
            'news/*',
            '*.ngrok-free.app/*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
