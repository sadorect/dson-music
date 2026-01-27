<?php

if (! function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\App\Settings\GeneralSettings
     */
    function setting($key = null, $default = null)
    {
        try {
            $settings = app(\App\Settings\GeneralSettings::class);

            // Try to access a property to check if settings are initialized
            $test = $settings->site_name;
        } catch (\Spatie\LaravelSettings\Exceptions\MissingSettings $e) {
            // Initialize settings with default values
            $settings = new \App\Settings\GeneralSettings([
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
                        'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
                    ],
                ],
            ]);

            // Add default values for your new settings
            $settings->site_description = 'Stream and download music from emerging artists worldwide';
            $settings->contact_email = 'contact@grinmusic.com';
            $settings->social_links = [
                'facebook' => 'https://facebook.com/grinmusic',
                'twitter' => 'https://twitter.com/grinmusic',
                'instagram' => 'https://instagram.com/grinmusic',
                'tiktok' => 'https://tiktok.com/grinmusic',
            ];
            $settings->enable_registration = true;
            $settings->footer_text = '© '.date('Y').' GRIN Music. All rights reserved.';
            // Add default values for logo settings
            $settings->logo_desktop_path = null;
            $settings->logo_desktop_url = null;
            $settings->logo_mobile_path = null;
            $settings->logo_mobile_url = null;
            $settings->favicon_path = null;
            $settings->favicon_url = null;

            $settings->save();
        }

        if (is_null($key)) {
            return $settings;
        }

        if (is_array($key)) {
            foreach ($key as $k => $value) {
                $settings->$k = $value;
            }
            $settings->save();

            return $settings;
        }

        return $settings->$key ?? $default;
    }
}
