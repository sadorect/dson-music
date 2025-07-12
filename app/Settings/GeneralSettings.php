<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public bool $maintenance_mode;
    public int $max_upload_size;
    public array $hero_slides;

 // Add your new settings here
 public string $site_description;
 public string $contact_email;
 public array $social_links;
 public bool $enable_registration;
 public string $footer_text;
 // Add any other settings you need
 public ?string $logo_desktop_path;
    public ?string $logo_desktop_url;
    public ?string $logo_mobile_path;
    public ?string $logo_mobile_url;
    public ?string $favicon_path;
    public ?string $favicon_url;
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
            ],
            'site_description' => 'A modern music streaming and distribution platform for independent artists',
            'contact_email' => 'support@grinmusic.com',
            'social_links' => [
                'facebook' => 'https://facebook.com/grinmusic',
                'twitter' => 'https://twitter.com/grinmusic',
                'instagram' => 'https://instagram.com/grinmusic'
            ],
            'enable_registration' => true,
            'footer_text' => '© 2025 GRIN Music. All rights reserved.',
            'logo_desktop_path' => null,
            'logo_desktop_url' => null,
            'logo_mobile_path' => null,
            'logo_mobile_url' => null,
            'favicon_path' => null,
            'favicon_url' => null
        ];
    }
}
