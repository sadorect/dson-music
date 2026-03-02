<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track front-end GET requests (not admin, not Livewire AJAX, not assets)
        if (
            $request->isMethod('GET')
            && !$request->is('musicdirector*')
            && !$request->is('livewire*')
            && !$request->expectsJson()
            && !$request->header('X-Livewire')
            && !$request->is('_ignition*')
        ) {
            try {
                PageView::create([
                    'path'       => '/' . ltrim($request->path(), '/'),
                    'session_id' => session()->getId(),
                    'user_id'    => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr($request->userAgent() ?? '', 0, 500),
                ]);
            } catch (\Throwable) {
                // Silently fail — never let analytics break a page request
            }
        }

        return $response;
    }
}
