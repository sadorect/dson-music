@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">Site Settings</h1>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <!-- Tabs for different settings sections -->
    <div x-data="{ activeTab: 'general' }" class="mb-8">
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'general'" :class="{'border-orange-500 text-orange-600': activeTab === 'general', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'general'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    General Settings
                </button>
                <button @click="activeTab = 'appearance'" :class="{'border-orange-500 text-orange-600': activeTab === 'appearance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'appearance'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Appearance
                </button>
                <button @click="activeTab = 'social'" :class="{'border-orange-500 text-orange-600': activeTab === 'social', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'social'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                  Social Links
              </button>
                <button @click="activeTab = 'content'" :class="{'border-orange-500 text-orange-600': activeTab === 'content', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'content'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Content Management
                </button>
            </nav>
        </div>

        <!-- General Settings Tab -->
        <div x-show="activeTab === 'general'">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium mb-4">General Settings</h2>
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="site_name">
                                Site Name
                            </label>
                            <input type="text" name="site_name" id="site_name" value="{{ setting('site_name', 'DSON Music') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            @error('site_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                          <label class="block text-gray-700 text-sm font-bold mb-2" for="site_description">
                              Site Description
                          </label>
                          <textarea name="site_description" id="site_description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3">{{ setting('site_description', '') }}</textarea>
                        </div>
        
                        <div class="mb-4">
                          <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_email">
                              Contact Email
                          </label>
                          <input type="email" name="contact_email" id="contact_email" value="{{ setting('contact_email', '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
        
                        <div class="mb-4">
                          <label class="block text-gray-700 text-sm font-bold mb-2" for="footer_text">
                              Footer Text
                          </label>
                          <input type="text" name="footer_text" id="footer_text" value="{{ setting('footer_text', '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="max_upload_size">
                                Max Upload Size (MB)
                            </label>
                            <input type="number" name="max_upload_size" id="max_upload_size" value="{{ setting('max_upload_size', 10) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            @error('max_upload_size')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="maintenance_mode" value="1" {{ setting('maintenance_mode') ? 'checked' : '' }} class="mr-2">
                                <span class="text-sm">Maintenance Mode</span>
                            </label>
                        </div>
                                  
                          <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="enable_registration" value="1" {{ setting('enable_registration', true) ? 'checked' : '' }} class="mr-2">
                                <span class="text-sm">Enable User Registration</span>
                            </label>
                          </div>
                          
                          
                        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                            Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Appearance Tab -->
        <div x-show="activeTab === 'appearance'" x-cloak>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium mb-4">Appearance Settings</h2>
                    
                    <!-- Logo Settings Section -->
                    <div class="mt-4 mb-6">
                        <h3 class="text-md font-medium mb-4">Logo Settings</h3>
                        
                        <!-- Desktop Logo -->
                        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-gray-700 font-bold">
                                    Desktop Logo
                                </label>
                                
                                @if(setting('logo_desktop_url'))
                                <form action="{{ route('admin.settings.delete-logo') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="type" value="logo_desktop">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Are you sure you want to delete this logo?')">
                                        Delete Current Logo
                                    </button>
                                </form>
                                @endif
                            </div>
                            
                            @if(setting('logo_desktop_url'))
                            <div class="mb-4 p-3 bg-white rounded border">
                                <p class="text-xs text-gray-500 mb-2">Current Logo:</p>
                                <img src="{{ setting('logo_desktop_url') }}" alt="Desktop Logo" class="h-12">
                            </div>
                            @endif
                            
                            <form action="{{ route('admin.settings.update-logo') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="logo_type" value="logo_desktop">
                                
                                <div class="mb-2">
                                    <input type="file" name="logo_file" id="logo_desktop" class="border p-2 w-full">
                                    <p class="text-xs text-gray-500 mt-1">Recommended size: 200x50px. Max file size: 2MB.</p>
                                    
                                    @error('logo_file')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <button type="submit" class="mt-2 bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                    Upload Desktop Logo
                                </button>
                            </form>
                        </div>
                        
                        <!-- Mobile Logo -->
                        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-gray-700 font-bold">
                                    Mobile Logo
                                </label>
                                
                                @if(setting('logo_mobile_url'))
                                <form action="{{ route('admin.settings.delete-logo') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="type" value="logo_mobile">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Are you sure you want to delete this logo?')">
                                        Delete Current Logo
                                    </button>
                                </form>
                                @endif
                            </div>
                            
                            @if(setting('logo_mobile_url'))
                            <div class="mb-4 p-3 bg-white rounded border">
                                <p class="text-xs text-gray-500 mb-2">Current Logo:</p>
                                <img src="{{ setting('logo_mobile_url') }}" alt="Mobile Logo" class="h-10">
                            </div>
                            @endif
                            
                            <form action="{{ route('admin.settings.update-logo') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="logo_type" value="logo_mobile">
                                
                                <div class="mb-2">
                                    <input type="file" name="logo_file" id="logo_mobile" class="border p-2 w-full">
                                    <p class="text-xs text-gray-500 mt-1">Recommended size: 120x40px. Max file size: 2MB.</p>
                                    
                                    @error('logo_file')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <button type="submit" class="mt-2 bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                    Upload Mobile Logo
                                </button>
                            </form>
                        </div>
                        
                        <!-- Favicon -->
                        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-gray-700 font-bold">
                                    Favicon
                                </label>
                                
                                @if(setting('favicon_url'))
                                <form action="{{ route('admin.settings.delete-logo') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="type" value="favicon">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Are you sure you want to delete this favicon?')">
                                        Delete Current Favicon
                                    </button>
                                </form>
                                @endif
                            </div>
                            
                            @if(setting('favicon_url'))
                            <div class="mb-4 p-3 bg-white rounded border">
                                <p class="text-xs text-gray-500 mb-2">Current Favicon:</p>
                                <img src="{{ setting('favicon_url') }}" alt="Favicon" class="h-8 w-8">
                            </div>
                            @endif
                            
                            <form action="{{ route('admin.settings.update-logo') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="logo_type" value="favicon">
                                
                                <div class="mb-2">
                                    <input type="file" name="logo_file" id="favicon" class="border p-2 w-full">
                                    <p class="text-xs text-gray-500 mt-1">Must be 32x32px. Max file size: 2MB.</p>
                                    
                                    @error('logo_file')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <button type="submit" class="mt-2 bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                    Upload Favicon
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Tab -->
        <div x-show="activeTab === 'social'" x-cloak>
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">Social Settings</h2>
                <form action="{{ route('admin.settings.update-social') }}" method="POST">
                    @csrf                  
          
                    <div class="mb-4">
                      <label class="block text-gray-700 text-sm font-bold mb-2" for="social_links[facebook]">
                          Facebook URL
                      </label>
                      <input type="url" name="social_links[facebook]" id="social_links[facebook]" 
                             value="{{ old('social_links.facebook', isset(setting('social_links')['facebook']) ? setting('social_links')['facebook'] : '') }}" 
                             class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                      <label class="block text-gray-700 text-sm font-bold mb-2" for="social_links[twitter]">
                          Twitter URL
                      </label>
                      <input type="url" name="social_links[twitter]" id="social_links[twitter]" 
                             value="{{ old('social_links.twitter', isset(setting('social_links')['twitter']) ? setting('social_links')['twitter'] : '') }}" 
                             class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                      <label class="block text-gray-700 text-sm font-bold mb-2" for="social_links[tiktok]">
                          Tiktok URL
                      </label>
                      <input type="url" name="social_links[tiktok]" id="social_links[tiktok]" 
                             value="{{ old('social_links.tiktok', isset(setting('social_links')['tiktok']) ? setting('social_links')['tiktok'] : '') }}" 
                             class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                      <label class="block text-gray-700 text-sm font-bold mb-2" for="social_links[instagram]">
                          Instagram URL
                      </label>
                      <input type="url" name="social_links[instagram]" id="social_links[instagram]" 
                             value="{{ old('social_links.instagram', isset(setting('social_links')['instagram']) ? setting('social_links')['instagram'] : '') }}" 
                             class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                      Save Settings
                  </button>
                </form>
            </div>
          </div>
        </div>

        <!-- Content Management Tab -->
        <div x-show="activeTab === 'content'" x-cloak>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium mb-4">Content Management</h2>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('admin.settings.hero-slides') }}" class="flex items-center text-blue-500 hover:text-blue-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Hero Slides Management
                            </a>
                        </li>
                        <!-- Add more content management links as needed -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium mb-4">System Information</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Laravel Version:</span>
                    <span>{{ app()->version() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">PHP Version:</span>
                    <span>{{ phpversion() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Environment:</span>
                    <span>{{ app()->environment() }}</span>
                </div>
            </div>
        </div>
      </div>
  

</div>
@endsection
