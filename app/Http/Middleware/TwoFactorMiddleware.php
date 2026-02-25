<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip 2FA check for authentication routes and 2FA routes
        $excludedRoutes = [
            'login',
            'register', 
            'logout',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            '2fa.setup',
            '2fa.enable',
            '2fa.disable',
            '2fa.challenge',
            '2fa.verify',
            '2fa.recovery-codes',
            '2fa.regenerate-codes'
        ];

        if (in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        // Only check 2FA for authenticated users
        if (Auth::check()) {
            $user = Auth::user();

            // Skip 2FA for admin users with bypass permission
            if ($user->is_admin && $this->adminCanBypass2FA($user)) {
                return $next($request);
            }

            // Check if user has 2FA enabled
            if ($user->google2fa_enabled_at) {
                // Check if 2FA is verified for this session
                if (!Session::get('2fa_verified', false)) {
                    // Redirect to 2FA challenge
                    return redirect()->route('2fa.challenge');
                }

                // Check if 2FA verification has expired (2 hours)
                $verifiedAt = Session::get('2fa_verified_at');
                if ($verifiedAt && now()->diffInMinutes($verifiedAt) > 120) {
                    // Clear 2FA verification and redirect to challenge
                    Session::forget(['2fa_verified', '2fa_verified_at']);
                    return redirect()->route('2fa.challenge');
                }

                // Refresh the verification time
                Session::put('2fa_verified_at', now());
            }

            // Check if account is locked
            if ($user->locked_until && now()->lt($user->locked_until)) {
                Auth::logout();
                Session::flush();
                
                return redirect()->route('login')
                    ->with('error', 'Account is temporarily locked. Please try again later.');
            }
        }

        return $next($request);
    }

    /**
     * Check if admin user can bypass 2FA
     */
    protected function adminCanBypass2FA($user): bool
    {
        // Admins can bypass 2FA if they have the permission
        // This can be extended to use a proper permissions system
        return config('auth.admin_bypass_2fa', false);
    }
}