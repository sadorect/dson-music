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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- General Settings Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4">General Settings</h2>
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="site_name">
                            Site Name
                        </label>
                        <input type="text" name="site_name" id="site_name" value="{{ setting('site_name', 'GRIN Music') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('site_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
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
                    
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Save Settings
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Content Management Card -->
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
</div>
@endsection
