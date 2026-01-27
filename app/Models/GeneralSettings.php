<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSettings extends Model
{
    protected $table = 'general_settings';

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'enable_registration' => 'boolean',
        'max_upload_size' => 'integer',
        'hero_slides' => 'array',
        'social_links' => 'array',
    ];

    protected $fillable = [
        'site_name',
        'maintenance_mode',
        'max_upload_size',
        'hero_slides',
        'site_description',
        'contact_email',
        'social_links',
        'enable_registration',
        'footer_text',
        'logo_desktop_path',
        'logo_desktop_url',
        'logo_mobile_path',
        'logo_mobile_url',
        'favicon_path',
        'favicon_url',
    ];
}
