@extends('layouts.admin')

@section('title', 'Hero Slides Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Hero Slides Management</h1>
        <a href="{{ route('admin.settings.index') }}" class="bg-gray-200 px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-300">
            Back to Settings
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <form action="{{ route('admin.settings.hero-slides.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="space-y-8">
                    @forelse($heroSlides as $index => $slide)
                    <div class="border rounded-lg p-6 relative">
                        <h3 class="text-lg font-medium mb-4">Slide {{ $index + 1 }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Current Image
                                    </label>
                                    @if(isset($slide['image_url']))
                                    <img src="{{ $slide['image_url'] }}" alt="Slide {{ $index + 1 }}" class="w-full h-48 object-cover rounded">
                                    @else
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded">
                                        <span class="text-gray-500">No image set</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="slides[{{ $index }}][image]">
                                        Upload New Image
                                    </label>
                                    <input type="file" name="slides[{{ $index }}][image]" id="slides[{{ $index }}][image]" class="w-full">
                                    @error("slides.{$index}.image")
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="slides[{{ $index }}][active]" value="1" {{ isset($slide['active']) && $slide['active'] ? 'checked' : '' }} class="mr-2">
                                        <span class="text-sm">Active</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="slides[{{ $index }}][title]">
                                        Title
                                    </label>
                                    <input type="text" name="slides[{{ $index }}][title]" id="slides[{{ $index }}][title]" value="{{ $slide['title'] ?? '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    @error("slides.{$index}.title")
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="slides[{{ $index }}][subtitle]">
                                        Subtitle
                                    </label>
                                    <input type="text" name="slides[{{ $index }}][subtitle]" id="slides[{{ $index }}][subtitle]" value="{{ $slide['subtitle'] ?? '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="slides[{{ $index }}][button_text]">
                                        Button Text
                                    </label>
                                    <input type="text" name="slides[{{ $index }}][button_text]" id="slides[{{ $index }}][button_text]" value="{{ $slide['button_text'] ?? 'Get Started' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="slides[{{ $index }}][button_url]">
                                        Button URL
                                    </label>
                                    <input type="text" name="slides[{{ $index }}][button_url]" id="slides[{{ $index }}][button_url]" value="{{ $slide['button_url'] ?? route('register') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="remove_slide" value="{{ $index }}" class="absolute top-4 right-4 text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to remove this slide?')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    @empty
                    <p class="text-gray-500">No slides yet. Add your first slide below.</p>
                    @endforelse
                </div>
                
                <div class="mt-6 flex justify-between">
                    <button type="submit" name="add_slide" value="1" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Add New Slide
                    </button>
                    
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
