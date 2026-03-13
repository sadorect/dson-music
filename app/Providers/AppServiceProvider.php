<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
            static $shared = null;

            if ($shared === null) {
                try {
                    $siteSettings = Schema::hasTable('site_settings')
                        ? SiteSetting::current()
                        : null;
                } catch (Throwable) {
                    $siteSettings = null;
                }

                $siteName = $siteSettings?->effective_site_name ?? 'GrinMuzik';
                $siteTitle = $siteSettings?->effective_site_title ?? $siteName;
                $siteUrl = url('/');
                $siteLogo = $siteSettings?->site_logo_url;
                $routeName = request()->route()?->getName();

                $defaultDescription = 'Discover independent music, explore fresh releases, support artists, and build playlists on GrinMuzik.';

                $routeSeo = [
                    'home' => [
                        'title' => 'Independent Music Streaming',
                        'description' => 'Stream independent music, discover featured artists, browse new releases, and support creators directly on GrinMuzik.',
                        'canonical' => route('home'),
                    ],
                    'browse' => [
                        'title' => 'Browse Music',
                        'description' => 'Browse independent tracks by genre, mood, and popularity on GrinMuzik.',
                        'canonical' => route('browse'),
                    ],
                    'charts' => [
                        'title' => 'Music Charts',
                        'description' => 'See the top-performing tracks, plays, likes, and downloads across GrinMuzik charts.',
                        'canonical' => route('charts'),
                    ],
                    'new-releases' => [
                        'title' => 'New Releases',
                        'description' => 'Catch the latest songs and fresh drops from independent artists on GrinMuzik.',
                        'canonical' => route('new-releases'),
                    ],
                    'playlists.public' => [
                        'title' => 'Public Playlists',
                        'description' => 'Explore public playlists curated by listeners and discover new music collections on GrinMuzik.',
                        'canonical' => route('playlists.public'),
                    ],
                    'about' => [
                        'title' => 'About',
                        'description' => 'Learn how GrinMuzik helps independent artists share music and connect with listeners.',
                        'canonical' => route('about'),
                    ],
                    'artist-guide' => [
                        'title' => 'Artist Guide',
                        'description' => 'Read the GrinMuzik artist guide for publishing music, growing your audience, and earning support.',
                        'canonical' => route('artist-guide'),
                    ],
                    'pricing' => [
                        'title' => 'Pricing and Donations',
                        'description' => 'Understand how listener donations and artist monetization work on GrinMuzik.',
                        'canonical' => route('pricing'),
                    ],
                    'privacy' => [
                        'title' => 'Privacy Policy',
                        'description' => 'Read the GrinMuzik privacy policy.',
                        'canonical' => route('privacy'),
                    ],
                    'terms' => [
                        'title' => 'Terms of Service',
                        'description' => 'Review the GrinMuzik terms of service.',
                        'canonical' => route('terms'),
                    ],
                    'contact' => [
                        'title' => 'Contact',
                        'description' => 'Get in touch with the GrinMuzik team.',
                        'canonical' => route('contact'),
                    ],
                    'search' => [
                        'title' => 'Search',
                        'description' => 'Search GrinMuzik for tracks, artists, albums, and playlists.',
                        'canonical' => route('search'),
                        'robots' => 'noindex,follow',
                    ],
                    'login' => [
                        'title' => 'Sign In',
                        'robots' => 'noindex,nofollow',
                    ],
                    'register' => [
                        'title' => 'Create Account',
                        'robots' => 'noindex,nofollow',
                    ],
                    'profile' => [
                        'title' => 'Profile',
                        'robots' => 'noindex,nofollow',
                    ],
                    'dashboard' => [
                        'title' => 'Dashboard',
                        'robots' => 'noindex,nofollow',
                    ],
                    'listener.dashboard' => [
                        'title' => 'My Music',
                        'robots' => 'noindex,nofollow',
                    ],
                    'listener.playlists' => [
                        'title' => 'My Playlists',
                        'robots' => 'noindex,nofollow',
                    ],
                    'listener.liked' => [
                        'title' => 'Liked Tracks',
                        'robots' => 'noindex,nofollow',
                    ],
                    'listener.history' => [
                        'title' => 'Play History',
                        'robots' => 'noindex,nofollow',
                    ],
                    'artist.dashboard' => [
                        'title' => 'Artist Dashboard',
                        'robots' => 'noindex,nofollow',
                    ],
                    'artist.tracks' => [
                        'title' => 'Artist Tracks',
                        'robots' => 'noindex,nofollow',
                    ],
                    'artist.upload-track' => [
                        'title' => 'Upload Track',
                        'robots' => 'noindex,nofollow',
                    ],
                    'artist.edit-track' => [
                        'title' => 'Edit Track',
                        'robots' => 'noindex,nofollow',
                    ],
                    'artist.albums' => [
                        'title' => 'Artist Albums',
                        'robots' => 'noindex,nofollow',
                    ],
                    'artist.create-album' => [
                        'title' => 'Create Album',
                        'robots' => 'noindex,nofollow',
                    ],
                ];

                $organizationSchema = array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => $siteName,
                    'url' => $siteUrl,
                    'logo' => $siteLogo,
                    'sameAs' => collect($siteSettings?->social_links ?? [])
                        ->pluck('url')
                        ->filter()
                        ->values()
                        ->all(),
                ]);

                $websiteSchema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $siteName,
                    'url' => $siteUrl,
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => route('search') . '?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ];

                $shared = [
                    'siteSettings' => $siteSettings,
                    'siteName' => $siteName,
                    'siteTitle' => $siteTitle,
                    'socialLinks' => $siteSettings?->social_links ?? [],
                    'defaultSeo' => [
                        'title' => $siteTitle,
                        'description' => $defaultDescription,
                        'canonical' => request()->url(),
                        'robots' => 'index,follow',
                        'type' => 'website',
                        'image' => $siteLogo,
                        'site_name' => $siteName,
                    ],
                    'routeSeo' => $routeSeo[$routeName] ?? [],
                    'organizationSchema' => $organizationSchema,
                    'websiteSchema' => $websiteSchema,
                ];
            }

            $view->with($shared);
        });
    }
}
