<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Session timeout in minutes (configurable via auth.session_timeout).
     */
    protected int $timeout;

    public function __construct()
    {
        $this->timeout = config('auth.session_timeout', 120);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $lastActivity = session('last_activity_at');

        if ($lastActivity && now()->diffInMinutes($lastActivity) >= $this->timeout) {
            Auth::logout();
            session()->flush();
            session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please log in again.'], 401);
            }

            return redirect()->route('login')
                ->with('status', 'Your session has expired due to inactivity. Please log in again.');
        }

        // Refresh last activity timestamp on every authenticated request
        session(['last_activity_at' => now()]);

        return $next($request);
    }
}
