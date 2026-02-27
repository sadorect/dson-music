@extends('layouts.glass-app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-16">
        <h1 class="text-5xl font-bold text-gray-900 mb-4 animate-fade-in">
            Welcome to <span class="text-primary">GrinMusic</span>
        </h1>
        <p class="text-xl text-gray-700 mb-8 animate-slide-up">
            Experience music streaming with a beautiful glassy design
        </p>
        @guest
            <div class="space-x-4">
                <a href="{{ route('register') }}" class="glass-btn-primary glass-btn-primary-hover inline-block">
                    Get Started
                </a>
                <a href="{{ route('login') }}" class="glass-btn glass-btn-hover inline-block">
                    Sign In
                </a>
            </div>
        @endguest
    </div>

    <!-- Featured Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <!-- Glass Card 1 -->
        <div class="glass-card glass-card-hover p-6">
            <div class="w-full h-48 bg-gradient-to-br from-primary/20 to-primary/40 rounded-lg mb-4"></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Trending Tracks</h3>
            <p class="text-gray-600 mb-4">Discover the hottest music right now</p>
            <button class="glass-btn glass-btn-hover w-full">Explore</button>
        </div>

        <!-- Glass Card 2 -->
        <div class="glass-card glass-card-hover p-6">
            <div class="w-full h-48 bg-gradient-to-br from-gray-300/20 to-gray-400/40 rounded-lg mb-4"></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">New Releases</h3>
            <p class="text-gray-600 mb-4">Fresh music from emerging artists</p>
            <button class="glass-btn glass-btn-hover w-full">Browse</button>
        </div>

        <!-- Glass Card 3 -->
        <div class="glass-card glass-card-hover p-6">
            <div class="w-full h-48 bg-gradient-to-br from-red-300/20 to-red-400/40 rounded-lg mb-4"></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Artist Profiles</h3>
            <p class="text-gray-600 mb-4">Connect with your favorite creators</p>
            <button class="glass-btn glass-btn-hover w-full">Discover</button>
        </div>
    </div>

    <!-- Glass Panel Section -->
    <div class="glass-panel glass-panel-hover p-8 mb-16">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Why Choose GrinMusic?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Premium Sound</h3>
                <p class="text-gray-600 text-sm">High-quality audio streaming with crystal clear sound</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Support Artists</h3>
                <p class="text-gray-600 text-sm">Direct donations help creators continue making music</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Modern Design</h3>
                <p class="text-gray-600 text-sm">Beautiful glassy interface that's a joy to use</p>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="text-center">
            <div class="text-3xl font-bold text-primary mb-2">10K+</div>
            <div class="text-gray-600">Tracks</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-primary mb-2">500+</div>
            <div class="text-gray-600">Artists</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-primary mb-2">50K+</div>
            <div class="text-gray-600">Users</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-primary mb-2">1M+</div>
            <div class="text-gray-600">Plays</div>
        </div>
    </div>
</div>
@endsection