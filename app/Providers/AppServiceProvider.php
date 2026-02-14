<?php

namespace App\Providers;

use App\Models\ArtistProfile;
use App\Models\PlayHistory;
use App\Models\Track;
use App\Observers\ArtistProfileObserver;
use App\Observers\PlayHistoryObserver;
use App\Observers\TrackObserver;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(\Illuminate\Http\Request $request): void
    {
        // Register model observers for cache invalidation
        Track::observe(TrackObserver::class);
        PlayHistory::observe(PlayHistoryObserver::class);
        ArtistProfile::observe(ArtistProfileObserver::class);

        // Global API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Upload rate limiting
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Upload limit exceeded. Please try again later.',
                        'retry_after' => 3600,
                    ], 429);
                });
        });

        // Download rate limiting
        RateLimiter::for('downloads', function (Request $request) {
            return Limit::perHour(50)->by($request->user()?->id ?: $request->ip());
        });

        // Search rate limiting
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Authentication rate limiting
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Global web rate limiting
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        if (! empty(env('NGROK_URL')) && $request->server->has('HTTP_X_ORIGINAL_HOST')) {
            $this->app['url']->forceRootUrl(env('NGROK_URL'));
        }

        RateLimiter::for('comment-actions', function ($request) {
            $key = optional($request->user())->id ?: $request->ip();

            return [
                Limit::perMinute(5)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'You are commenting too quickly. Please slow down and try again shortly.',
                    ], 429);
                }),
                Limit::perHour(20)->by($key),
            ];
        });
    }
}
