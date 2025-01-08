<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventImpersonationAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('impersonated_by')) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
