<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public bool $maintenance_mode;
    public int $max_upload_size;
    public array $hero_slides;

    public static function group(): string
    {
        return 'general';
    }

    public static function default(): array
    {
        return [
            'site_name' => 'GRIN Music',
            'maintenance_mode' => false,
            'max_upload_size' => 10,
            'hero_slides' => [
                [
                    'title' => 'Discover New Music',
                    'subtitle' => 'Stream and download tracks from emerging artists worldwide',
                    'button_text' => 'Get Started',
                    'button_url' => '/register',
                    'active' => true,
                    'image_path' => null,
                    'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'
                ]
            ]
        ];
    }
}
