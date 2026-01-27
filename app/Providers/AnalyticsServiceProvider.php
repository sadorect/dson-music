<?php

namespace App\Providers;

use App\Services\Analytics\AnalyticsService;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService;
        });
    }
}
