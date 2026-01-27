<?php

namespace App\Providers;

use App\Models\ArtistProfile;
use App\Models\PlayHistory;
use App\Models\Track;
use App\Observers\ArtistProfileObserver;
use App\Observers\PlayHistoryObserver;
use App\Observers\TrackObserver;
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
