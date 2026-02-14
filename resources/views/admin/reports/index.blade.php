@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
            <p class="mt-2 text-sm text-gray-600">View and export various reports and analytics</p>
        </div>
        
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Available Reports</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Analytics Report -->
                    <a href="{{ route('admin.analytics.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-500 transition-all duration-200">
                        <div class="flex items-center mb-4">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Analytics Dashboard</h3>
                        <p class="text-sm text-gray-600">View detailed analytics, charts, and performance metrics</p>
                    </a>

                    <!-- Track Reports -->
                    <a href="{{ route('admin.tracks.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-500 transition-all duration-200">
                        <div class="flex items-center mb-4">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Track Reports</h3>
                        <p class="text-sm text-gray-600">Review all tracks, uploads, and approval status</p>
                    </a>

                    <!-- User Reports -->
                    <a href="{{ route('admin.users.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-500 transition-all duration-200">
                        <div class="flex items-center mb-4">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">User Statistics</h3>
                        <p class="text-sm text-gray-600">View user demographics, registration trends, and activity</p>
                    </a>

                    <!-- Artist Reports -->
                    <a href="{{ route('admin.artists.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-500 transition-all duration-200">
                        <div class="flex items-center mb-4">
                            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Artist Reports</h3>
                        <p class="text-sm text-gray-600">Artist profiles, verification status, and content metrics</p>
                    </a>

                    <!-- Track Review -->
                    <a href="{{ route('admin.tracks.review.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-500 transition-all duration-200">
                        <div class="flex items-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Pending Reviews</h3>
                        <p class="text-sm text-gray-600">Tracks awaiting approval and moderation queue</p>
                    </a>

                    <!-- Analytics Export -->
                    <a href="{{ route('admin.analytics.export') }}" class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-500 transition-all duration-200">
                        <div class="flex items-center mb-4">
                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Export Data</h3>
                        <p class="text-sm text-gray-600">Download analytics data and generate custom reports</p>
                    </a>
                </div>

                <!-- Quick Stats Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <p class="text-sm text-indigo-600 font-medium">Total Users</p>
                            <p class="text-2xl font-bold text-indigo-900 mt-1">{{ \App\Models\User::count() }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-green-600 font-medium">Total Tracks</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ \App\Models\Track::count() }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <p class="text-sm text-purple-600 font-medium">Total Artists</p>
                            <p class="text-2xl font-bold text-purple-900 mt-1">{{ \App\Models\ArtistProfile::count() }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-yellow-600 font-medium">Pending Reviews</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ \App\Models\Track::where('approval_status', 'pending')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
