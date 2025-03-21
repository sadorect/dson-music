<?php

namespace Database\Seeders;

use App\Settings\GeneralSettings;
use Illuminate\Database\Seeder;

class GeneralSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = app(GeneralSettings::class);
        
        $settings->site_name = 'GRIN Music';
        $settings->maintenance_mode = false;
        $settings->max_upload_size = 10;
        $settings->hero_slides = [
            [
                'title' => 'Discover New Music',
                'subtitle' => 'Stream and download tracks from emerging artists worldwide',
                'button_text' => 'Get Started',
                'button_url' => '/register',
                'active' => true,
                'image_path' => null,
                'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'
            ]
        ];
        
        $settings->save();
    }
}
