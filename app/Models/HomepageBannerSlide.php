<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomepageBannerSlide extends Model
{
    protected $fillable = [
        'name',
        'badge_text',
        'heading',
        'body',
        'primary_button_label',
        'primary_button_url',
        'secondary_button_label',
        'secondary_button_url',
        'background_image',
        'background_image_alt',
        'show_overlay_content',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'show_overlay_content' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function getBackgroundUrlAttribute(): ?string
    {
        if (!$this->background_image) {
            return null;
        }

        if (Str::startsWith($this->background_image, ['http://', 'https://'])) {
            return $this->background_image;
        }

        return Storage::disk('public')->url($this->background_image);
    }

    public function getEffectiveBackgroundAltAttribute(): string
    {
        return $this->background_image_alt
            ?: ($this->heading ?: $this->name ?: 'Homepage banner');
    }
}
