<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // API rate limiting - 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Upload rate limiting - 10 uploads per hour
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Upload limit exceeded. Please try again later.',
                        'retry_after' => 3600
                    ], 429);
                });
        });

        // Download rate limiting - 50 downloads per hour
        RateLimiter::for('downloads', function (Request $request) {
            return Limit::perHour(50)->by($request->user()?->id ?: $request->ip());
        });

        // Search rate limiting - 30 searches per minute
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Authentication rate limiting - 5 login attempts per minute
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Global web rate limiting - 100 requests per minute
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        });
    }
}
