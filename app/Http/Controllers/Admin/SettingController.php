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
            'max_upload_size' => 'required|integer'
        ]);

        $this->settings->site_name = $validated['site_name'];
        $this->settings->maintenance_mode = $request->has('maintenance_mode');
        $this->settings->max_upload_size = $validated['max_upload_size'];
        $this->settings->save();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
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
