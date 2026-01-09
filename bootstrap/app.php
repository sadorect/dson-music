<?php

use App\Console\Commands\BackfillDownloadCounts;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        BackfillDownloadCounts::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'artist.profile.complete' => \App\Http\Middleware\EnsureArtistProfileComplete::class,
            'recaptcha' => \App\Http\Middleware\VerifyReCaptcha::class,
            'PreventImpersonationAccess' => \App\Http\Middleware\PreventImpersonationAccess::class,
            'query.monitor' => \App\Http\Middleware\QueryMonitoring::class,
        ]);
        
        // Add QueryMonitoring to web middleware group in development
        if (env('APP_DEBUG', false)) {
            $middleware->web(append: [
                \App\Http\Middleware\QueryMonitoring::class,
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
