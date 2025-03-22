<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected $settings;

    public function __construct(GeneralSettings $settings)
    {
        $this->settings = $settings;
    }

    public function index()
    {
        return view('admin.settings.index', [
            'settings' => $this->settings
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'maintenance_mode' => 'boolean',
            'max_upload_size' => 'required|integer',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email',
            'social_links.facebook' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
            'social_links.tiktok' => 'nullable|url',
            'enable_registration' => 'boolean',
            'footer_text' => 'nullable|string|max:255',
        ]);
    
        // Update regular settings
        $this->settings->site_name = $validated['site_name'];
        $this->settings->maintenance_mode = $request->has('maintenance_mode');
        $this->settings->max_upload_size = $validated['max_upload_size'];
        
        // Update optional settings if present
        if (isset($validated['site_description'])) {
            $this->settings->site_description = $validated['site_description'];
        }
        
        if (isset($validated['contact_email'])) {
            $this->settings->contact_email = $validated['contact_email'];
        }
        
        if (isset($validated['enable_registration'])) {
            $this->settings->enable_registration = $validated['enable_registration'];
        }
        
        if (isset($validated['footer_text'])) {
            $this->settings->footer_text = $validated['footer_text'];
        }
        
        // Handle social links if present
        $socialLinks = [];
        foreach (['facebook', 'twitter', 'instagram', 'tiktok'] as $platform) {
            $key = "social_links.$platform";
            if (isset($validated[$key])) {
                $socialLinks[$platform] = $validated[$key];
            }
        }
        
        if (!empty($socialLinks)) {
            // Merge with existing social links to avoid overwriting ones not in the form
            $currentSocialLinks = $this->settings->social_links ?? [];
            $this->settings->social_links = array_merge($currentSocialLinks, $socialLinks);
        }
        
        // Save all settings
        $this->settings->save();
    
        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
    }
    


    public function updateSocial(Request $request)
{
    $validated = $request->validate([
        'social_links.facebook' => 'nullable|url',
        'social_links.twitter' => 'nullable|url',
        'social_links.instagram' => 'nullable|url',
        'social_links.tiktok' => 'nullable|url',
    ]);

    // Initialize social_links if it doesn't exist
    if (!isset($this->settings->social_links)) {
        $this->settings->social_links = [];
    }
    
    // Get the current social links
    $currentSocialLinks = $this->settings->social_links;
    
    // Update each social link
    foreach (['facebook', 'twitter', 'instagram', 'tiktok'] as $platform) {
        $key = "social_links.$platform";
        if (isset($validated[$key])) {
            $currentSocialLinks[$platform] = $validated[$key];
        }
    }
    
    // Update the settings
    $this->settings->social_links = $currentSocialLinks;
    
    // Save the settings
    $this->settings->save();
    
    // For debugging
    \Log::info('Social settings updated', [
        'social_links' => $this->settings->social_links
    ]);

    return redirect()->route('admin.settings.index')
        ->with('success', 'Social Settings updated successfully');
}

    
    
/**
 * Update logo settings
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
    public function updateLogo(Request $request)
    {
        
        $request->validate([
            'logo_type' => 'required|in:logo_desktop,logo_mobile,favicon',
            'logo_file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Add specific validation for favicon dimensions
        if ($request->logo_type === 'favicon') {
            $request->validate([
                'logo_file' => 'dimensions:width=32,height=32',
            ]);
        }

        $type = $request->logo_type;
        $file = $request->file('logo_file');

        // Delete old file if exists
        $oldPath = setting("{$type}_path");
        if ($oldPath) {
            Storage::disk('s3')->delete($oldPath);
        }

        // Store the new file
        $path = $file->store('logos', 's3');
        $url = Storage::disk('s3')->url($path);

        // Update settings
        setting([
            "{$type}_path" => $path,
            "{$type}_url" => $url,
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', ucfirst(str_replace('_', ' ', $type)) . ' updated successfully');
    }


/**
 * Delete a logo
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
    public function deleteLogo(Request $request)
    {
        $request->validate([
            'type' => 'required|in:logo_desktop,logo_mobile,favicon',
        ]);
        
        $type = $request->type;
        $path = setting("{$type}_path");
        
        if ($path) {
            Storage::disk('s3')->delete($path);
        }
        
        setting([
            "{$type}_path" => null,
            "{$type}_url" => null,
        ]);
        
        return redirect()->route('admin.settings.index')
        ->with('success', ucfirst(str_replace('_', ' ', $type)) . ' deleted successfully');
    }

    public function heroSlides()
    {
        return view('admin.settings.hero-slides', [
            'heroSlides' => $this->settings->hero_slides
        ]);
    }
    
    public function updateHeroSlides(Request $request)
    {
        $request->validate([
            'slides.*.image' => 'nullable|image|max:2048',
            'slides.*.title' => 'required|string|max:255',
            'slides.*.subtitle' => 'nullable|string|max:255',
            'slides.*.button_text' => 'nullable|string|max:50',
            'slides.*.button_url' => 'nullable|string|max:255',
            'slides.*.active' => 'boolean',
        ]);
        
        $heroSlides = $this->settings->hero_slides;
        $updatedSlides = [];
        
        foreach ($request->slides as $index => $slideData) {
            $slide = isset($heroSlides[$index]) ? $heroSlides[$index] : [];
            
            // Handle image upload
            if ($request->hasFile("slides.{$index}.image")) {
                // Delete old image if exists
                if (isset($slide['image_path']) && $slide['image_path']) {
                    Storage::disk('s3')->delete($slide['image_path']);
                }
                
                // Upload new image to S3
                $path = $request->file("slides.{$index}.image")->store('hero-slides', 's3');
                $slide['image_path'] = $path;
                $slide['image_url'] = Storage::disk('s3')->url($path);
            }
            
            $slide['title'] = $slideData['title'];
            $slide['subtitle'] = $slideData['subtitle'] ?? '';
            $slide['button_text'] = $slideData['button_text'] ?? 'Get Started';
            $slide['button_url'] = $slideData['button_url'] ?? route('register');
            $slide['active'] = isset($slideData['active']);
            
            $updatedSlides[] = $slide;
        }
        
        // Add new slide if requested
        if ($request->has('add_slide')) {
            $updatedSlides[] = [
                'title' => 'New Slide',
                'subtitle' => 'Add your subtitle here',
                'button_text' => 'Get Started',
                'button_url' => route('register'),
                'active' => true,
                'image_path' => null,
                'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'
            ];
        }
        
        // Remove slide if requested
        if ($request->has('remove_slide') && isset($updatedSlides[$request->remove_slide])) {
            // Delete image from S3 if exists
            if (isset($updatedSlides[$request->remove_slide]['image_path'])) {
                Storage::disk('s3')->delete($updatedSlides[$request->remove_slide]['image_path']);
            }
            
            array_splice($updatedSlides, $request->remove_slide, 1);
        }
        
        $this->settings->hero_slides = $updatedSlides;
        $this->settings->save();
        
        return redirect()->route('admin.settings.hero-slides')
            ->with('success', 'Hero slides updated successfully');
    }
}
