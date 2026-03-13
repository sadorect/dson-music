<?php

namespace App\Support;

use App\Models\SiteSetting;

class UploadLimits
{
    public const DEFAULT_AUDIO_KB = 10240;

    public const DEFAULT_IMAGE_KB = 2048;

    public const DEFAULT_SITE_LOGO_KB = 2048;

    public const DEFAULT_FAVICON_KB = 512;

    public const DEFAULT_HERO_IMAGE_KB = 4096;

    protected static ?array $cachedSettings = null;

    public static function audioKb(): int
    {
        return static::setting('audio_upload_limit_kb', static::DEFAULT_AUDIO_KB);
    }

    public static function imageKb(): int
    {
        return static::setting('image_upload_limit_kb', static::DEFAULT_IMAGE_KB);
    }

    public static function siteLogoKb(): int
    {
        return static::setting('site_logo_upload_limit_kb', static::DEFAULT_SITE_LOGO_KB);
    }

    public static function faviconKb(): int
    {
        return static::setting('favicon_upload_limit_kb', static::DEFAULT_FAVICON_KB);
    }

    public static function heroImageKb(): int
    {
        return static::setting('hero_image_upload_limit_kb', static::DEFAULT_HERO_IMAGE_KB);
    }

    public static function audioBytes(): int
    {
        return static::audioKb() * 1024;
    }

    public static function imageBytes(): int
    {
        return static::imageKb() * 1024;
    }

    public static function formatKilobytes(int $kilobytes): string
    {
        if ($kilobytes >= 1024) {
            $megabytes = $kilobytes / 1024;

            return fmod($megabytes, 1.0) === 0.0
                ? number_format($megabytes, 0) . ' MB'
                : number_format($megabytes, 1) . ' MB';
        }

        return number_format($kilobytes) . ' KB';
    }

    public static function resetCache(): void
    {
        static::$cachedSettings = null;
    }

    protected static function setting(string $attribute, int $fallback): int
    {
        try {
            if (! SiteSetting::supportsUploadLimits()) {
                return $fallback;
            }

            $settings = static::$cachedSettings ??= SiteSetting::current()->only([
                'audio_upload_limit_kb',
                'image_upload_limit_kb',
                'site_logo_upload_limit_kb',
                'favicon_upload_limit_kb',
                'hero_image_upload_limit_kb',
            ]);

            $value = (int) ($settings[$attribute] ?? 0);

            return $value > 0 ? $value : $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
