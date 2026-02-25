<?php

namespace App\Http\Kernel;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        // ... other middlewares

        'auth.basic'                 => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'artist.profile.complete'    => \App\Http\Middleware\EnsureArtistProfileComplete::class,
        'recaptcha'                  => \App\Http\Middleware\VerifyReCaptcha::class,
        'admin'                      => \App\Http\Middleware\AdminMiddleware::class,
        'PreventImpersonationAccess' => \App\Http\Middleware\PreventImpersonationAccess::class,
        '2fa'                        => \App\Http\Middleware\TwoFactorMiddleware::class,
        'session.timeout'            => \App\Http\Middleware\SessionTimeoutMiddleware::class,
        'password.policy'            => \App\Http\Middleware\PasswordPolicyMiddleware::class,
    ];

    protected $middleware = [
        // ... other middleware
        // \App\Http\Middleware\HandlePostSizeErrors::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\CSPMiddleware::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'PreventImpersonationAccess' => \App\Http\Middleware\PreventImpersonationAccess::class,
    ];

    protected $routeMiddleware = [
        // ... other middlewares
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'PreventImpersonationAccess' => \App\Http\Middleware\PreventImpersonationAccess::class,
    ];

    protected $commands = [
        \App\Console\Commands\MigrateFilesToS3::class,
    ];
}
