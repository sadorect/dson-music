<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VerifyReCaptcha
{
    public function handle(Request $request, Closure $next)
    {
        if ($token = $request->input('recaptcha_token')) {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $token,
                'ip' => $request->ip(),
            ]);

            if ($response->successful() && $response->json('success') && $response->json('score') > 0.5) {
                return $next($request);
            }
        }

        return redirect()->back()->with('error', 'Invalid reCAPTCHA verification');
    }
}
