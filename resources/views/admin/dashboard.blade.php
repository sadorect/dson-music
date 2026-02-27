@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-red-50/20 to-gray-100">
    <!-- Admin Header -->
    <div class="bg-glass-base border-glass border/50 backdrop-blur-2xl shadow-glass p-6 rounded-2xl mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.942 2.942a1.724 1.724 0 001.065 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.942 2.942a1.724 1.724 0 00-2.573 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.942-2.942a1.724 1.724 0 00-1.065-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.942-2.942a1.724 1.724 0 002.573-1.065z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Admin Dashboard</h1>
                        <p class="text-gray-300">Super Admin Access</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm">
                        Super Admin
                    </span>
                    <a href="{{ route('logout') }}" class="text-red-500 hover:text-red-600 transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-glass-base border-glass border/50 backdrop-blur-lg shadow-glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Total Users</p>
                        <p class="text-2xl font-bold text-white">1,234</p>
                    </div>
                    <div class="bg-red-500 rounded-full w-12 h-12 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-glass-base border-glass border/50 backdrop-blur-lg shadow-glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Total Artists</p>
                        <p class="text-2xl font-bold text-white">87</p>
                    </div>
                    <div class="bg-red-500 rounded-full w-12 h-12 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.182-8.182z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-glass-base border-glass border/50 backdrop-blur-lg shadow-glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Total Tracks</p>
                        <p class="text-2xl font-bold text-white">4,567</p>
                    </div>
                    <div class="bg-red-500 rounded-full w-12 h-12 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l-3 2V6a3 3 0 013-3h4a3 3 0 013 3v2l-3 2v5a3 3 0 01-3 3H5a3 3 0 01-3-3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-glass-base border-glass border/50 backdrop-blur-lg shadow-glass rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Total Plays</p>
                        <p class="text-2xl font-bold text-white">98,765</p>
                    </div>
                    <div class="bg-red-500 rounded-full w-12 h-12 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="bg-glass-base border-glass border/50 backdrop-blur-2xl shadow-glass rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.users') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                    Manage Users
                </a>
                <a href="{{ route('admin.artists') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                    Manage Artists
                </a>
                <a href="{{ route('admin.tracks') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                    Manage Tracks
                </a>
                <a href="{{ route('admin.settings') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                    Site Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-glass-base border-glass border/50 backdrop-blur-2xl shadow-glass rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">Recent Activity</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-glass-base/50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white">New user registered</p>
                            <p class="text-sm text-gray-300">2 minutes ago</p>
                        </div>
                    </div>
                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs">Success</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-glass-base/50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.182-8.182z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white">Artist approved</p>
                            <p class="text-sm text-gray-300">15 minutes ago</p>
                        </div>
                    </div>
                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs">Success</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-glass-base/50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white">Track uploaded</p>
                            <p class="text-sm text-gray-300">1 hour ago</p>
                        </div>
                    </div>
                    <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs">Pending</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection