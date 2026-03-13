<?php

namespace App\Models;

use App\Support\UploadLimits;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSetting extends Model
{
    protected static ?bool $supportsDiscoveryVisibilityCache = null;
    protected static ?bool $supportsDiscoveryOrderingCache = null;
    protected static ?bool $supportsUploadLimitsCache = null;

    protected $fillable = [
        'site_name',
        'site_title',
        'x_handle',
        'instagram_handle',
        'facebook_handle',
        'tiktok_handle',
        'youtube_handle',
        'site_logo',
        'favicon',
        'audio_upload_limit_kb',
        'image_upload_limit_kb',
        'site_logo_upload_limit_kb',
        'favicon_upload_limit_kb',
        'hero_image_upload_limit_kb',
        'show_home_personalized',
        'show_home_editor_picks',
        'show_browse_mood_filters',
        'show_browse_editor_picks',
        'show_browse_personalized',
        'show_browse_fresh_this_week',
        'show_browse_artists_to_watch',
        'show_browse_support_direct',
        'show_search_trending_tracks',
        'show_search_popular_artists',
        'home_editor_picks_position',
        'browse_editor_picks_position',
    ];

    protected $casts = [
        'show_home_personalized' => 'boolean',
        'show_home_editor_picks' => 'boolean',
        'audio_upload_limit_kb' => 'integer',
        'image_upload_limit_kb' => 'integer',
        'site_logo_upload_limit_kb' => 'integer',
        'favicon_upload_limit_kb' => 'integer',
        'hero_image_upload_limit_kb' => 'integer',
        'show_browse_mood_filters' => 'boolean',
        'show_browse_editor_picks' => 'boolean',
        'show_browse_personalized' => 'boolean',
        'show_browse_fresh_this_week' => 'boolean',
        'show_browse_artists_to_watch' => 'boolean',
        'show_browse_support_direct' => 'boolean',
        'show_search_trending_tracks' => 'boolean',
        'show_search_popular_artists' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public static function supportsDiscoveryVisibility(): bool
    {
        if (static::$supportsDiscoveryVisibilityCache !== null) {
            return static::$supportsDiscoveryVisibilityCache;
        }

        try {
            if (! Schema::hasTable('site_settings')) {
                return static::$supportsDiscoveryVisibilityCache = false;
            }

            return static::$supportsDiscoveryVisibilityCache = Schema::hasColumn('site_settings', 'show_home_personalized');
        } catch (Throwable) {
            return static::$supportsDiscoveryVisibilityCache = false;
        }
    }

    public static function supportsDiscoveryOrdering(): bool
    {
        if (static::$supportsDiscoveryOrderingCache !== null) {
            return static::$supportsDiscoveryOrderingCache;
        }

        try {
            if (! Schema::hasTable('site_settings')) {
                return static::$supportsDiscoveryOrderingCache = false;
            }

            return static::$supportsDiscoveryOrderingCache = Schema::hasColumn('site_settings', 'home_editor_picks_position');
        } catch (Throwable) {
            return static::$supportsDiscoveryOrderingCache = false;
        }
    }

    public static function supportsUploadLimits(): bool
    {
        if (static::$supportsUploadLimitsCache !== null) {
            return static::$supportsUploadLimitsCache;
        }

        try {
            if (! Schema::hasTable('site_settings')) {
                return static::$supportsUploadLimitsCache = false;
            }

            return static::$supportsUploadLimitsCache = Schema::hasColumn('site_settings', 'audio_upload_limit_kb');
        } catch (Throwable) {
            return static::$supportsUploadLimitsCache = false;
        }
    }

    public function getSiteLogoUrlAttribute(): ?string
    {
        return $this->buildPublicAssetUrl($this->site_logo);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->buildPublicAssetUrl($this->favicon);
    }

    protected function buildPublicAssetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $url = Storage::disk('public')->url($path);
        $version = $this->updated_at?->timestamp;

        return $version ? "{$url}?v={$version}" : $url;
    }

    public function getEffectiveSiteNameAttribute(): string
    {
        return $this->site_name ?: 'GrinMuzik';
    }

    public function getEffectiveSiteTitleAttribute(): string
    {
        return $this->site_title ?: $this->effective_site_name;
    }

    public function getEffectiveAudioUploadLimitKbAttribute(): int
    {
        return (int) ($this->audio_upload_limit_kb ?: UploadLimits::DEFAULT_AUDIO_KB);
    }

    public function getEffectiveImageUploadLimitKbAttribute(): int
    {
        return (int) ($this->image_upload_limit_kb ?: UploadLimits::DEFAULT_IMAGE_KB);
    }

    public function getEffectiveSiteLogoUploadLimitKbAttribute(): int
    {
        return (int) ($this->site_logo_upload_limit_kb ?: UploadLimits::DEFAULT_SITE_LOGO_KB);
    }

    public function getEffectiveFaviconUploadLimitKbAttribute(): int
    {
        return (int) ($this->favicon_upload_limit_kb ?: UploadLimits::DEFAULT_FAVICON_KB);
    }

    public function getEffectiveHeroImageUploadLimitKbAttribute(): int
    {
        return (int) ($this->hero_image_upload_limit_kb ?: UploadLimits::DEFAULT_HERO_IMAGE_KB);
    }

    public function getSocialLinksAttribute(): array
    {
        $links = [
            [
                'label' => 'Twitter/X',
                'handle' => $this->x_handle,
                'url' => $this->normalizeSocialUrl($this->x_handle, 'https://x.com/'),
                'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z',
            ],
            [
                'label' => 'Instagram',
                'handle' => $this->instagram_handle,
                'url' => $this->normalizeSocialUrl($this->instagram_handle, 'https://www.instagram.com/'),
                'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z',
            ],
            [
                'label' => 'Facebook',
                'handle' => $this->facebook_handle,
                'url' => $this->normalizeSocialUrl($this->facebook_handle, 'https://www.facebook.com/'),
                'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
            ],
            [
                'label' => 'TikTok',
                'handle' => $this->tiktok_handle,
                'url' => $this->normalizeSocialUrl($this->tiktok_handle, 'https://www.tiktok.com/@', prefixAt: true),
                'path' => 'M19.589 6.686a4.793 4.793 0 01-3.77-4.235V2h-3.193v13.402a2.039 2.039 0 01-2.039 2.039 2.039 2.039 0 010-4.078c.167 0 .328.022.482.06V10.17a5.266 5.266 0 00-.482-.023A5.232 5.232 0 005.355 15.38a5.232 5.232 0 005.232 5.232 5.232 5.232 0 005.232-5.232V9.693a8.003 8.003 0 004.181 1.175V7.675a4.77 4.77 0 01-.411-.989z',
            ],
            [
                'label' => 'YouTube',
                'handle' => $this->youtube_handle,
                'url' => $this->normalizeSocialUrl($this->youtube_handle, 'https://www.youtube.com/@', prefixAt: true),
                'path' => 'M23.498 6.186a2.997 2.997 0 00-2.11-2.12C19.536 3.5 12 3.5 12 3.5s-7.536 0-9.388.566a2.997 2.997 0 00-2.11 2.12C0 8.05 0 12 0 12s0 3.95.502 5.814a2.997 2.997 0 002.11 2.12C4.464 20.5 12 20.5 12 20.5s7.536 0 9.388-.566a2.997 2.997 0 002.11-2.12C24 15.95 24 12 24 12s0-3.95-.502-5.814zM9.75 15.568V8.432L15.818 12 9.75 15.568z',
            ],
        ];

        return array_values(array_filter($links, fn (array $link): bool => filled($link['url'])));
    }

    protected function normalizeSocialUrl(?string $value, string $baseUrl, bool $prefixAt = false): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $value = trim($value);

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $value = ltrim($value, '@/');

        if (! filled($value)) {
            return null;
        }

        if ($prefixAt) {
            return $baseUrl . $value;
        }

        return $baseUrl . $value;
    }
}
