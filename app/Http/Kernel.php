<?php

namespace App\Http\Kernel;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        // ... other middlewares
       
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'artist.profile.complete' => \App\Http\Middleware\EnsureArtistProfileComplete::class,
        'recaptcha' => \App\Http\Middleware\VerifyReCaptcha::class,
    ];
}
