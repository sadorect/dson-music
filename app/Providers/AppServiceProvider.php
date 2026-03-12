<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            static $branding = null;

            if ($branding === null) {
                $siteSettings = Schema::hasTable('site_settings')
                    ? SiteSetting::current()
                    : null;

                $branding = [
                    'siteSettings' => $siteSettings,
                    'siteName' => $siteSettings?->effective_site_name ?? 'GrinMuzik',
                    'siteTitle' => $siteSettings?->effective_site_title ?? 'GrinMuzik',
                    'socialLinks' => $siteSettings?->social_links ?? [],
                ];
            }

            $view->with($branding);
        });
    }
}
