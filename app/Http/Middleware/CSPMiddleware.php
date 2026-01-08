<?php

namespace App\Http\Middleware;

use Closure;

class CSPMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Generate nonce for inline scripts
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        
        // Build Content Security Policy with nonce
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://cdnjs.cloudflare.com https://unpkg.com",
            "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https: blob:",
            "media-src 'self' blob: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ];
        
        // Set CSP header
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        
        // Set additional security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        return $response;
    }
}
