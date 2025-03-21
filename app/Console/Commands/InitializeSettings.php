<?php

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use Illuminate\Console\Command;

class InitializeSettings extends Command
{
    protected $signature = 'settings:initialize';
    protected $description = 'Initialize application settings with default values';

    public function handle()
    {
        $this->info('Initializing application settings...');
        
        $settings = new GeneralSettings([
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
        ]);
        
        $settings->save();
        
        $this->info('Settings initialized successfully!');
    }
}
