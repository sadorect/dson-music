<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Analytics\AnalyticsService;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService();
        });
    }
}
