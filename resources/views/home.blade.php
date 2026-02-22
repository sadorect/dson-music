@extends('layouts.app')

@section('title', 'GRIN Music - Stream Your Favorite Music')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Gradient Mesh Background (Decorative) -->
    <div class="gradient-mesh fixed inset-0 pointer-events-none"></div>
    
    <div class="relative z-10 p-3 sm:p-4 lg:p-6">
        
        <!-- HERO / DISCOVERY ENTRY POINT -->
        <section class="mb-8 sm:mb-10 lg:mb-12 section-spacing">
            <div class="bg-white border border-black/10 rounded-2xl p-6 sm:p-8 shadow-sm">
                <div class="max-w-3xl">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-black mb-3">
                        Welcome to GRIN Music
                    </h1>
                    <p class="text-lg sm:text-xl text-black/70 mb-6">
                        Discover trending tracks, connect with artists, and build your unique sound library.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('search') }}" class="glass-btn-primary">
                            <span>Explore Music</span>
                        </a>
                        <a href="{{ route('library.index') }}" class="glass-btn">
                            <span class="text-black">My Library</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- TRENDING TRACKS STRIP -->
        <section class="mb-8 sm:mb-10 lg:mb-12 section-spacing">
            <x-home.trending :tracks="$trendingTracks" title="🔥 Trending Now" />
        </section>

        <!-- POPULAR ARTISTS STRIP -->
        <section class="mb-8 sm:mb-10 lg:mb-12 section-spacing">
            <x-home.popular-artists title="⭐ Today's Top Artists" :artists="$featuredArtists" />
        </section>

        <!-- FEATURED COLLECTIONS -->
        <section class="mb-8 sm:mb-10 lg:mb-12 section-spacing">
            <div class="mb-4">
                <h2 class="text-2xl sm:text-3xl font-bold text-black">📀 Featured Collections</h2>
                <p class="text-sm text-black/60 mt-1">Hand-picked playlists and albums for you</p>
            </div>
            <x-home.featured-artists title="" :artists="$featuredArtists" />
        </section>

        <!-- NEW RELEASES -->
        <section class="mb-8 sm:mb-10 lg:mb-12 section-spacing">
            <div class="mb-4">
                <h2 class="text-2xl sm:text-3xl font-bold text-black">✨ New Releases</h2>
                <p class="text-sm text-black/60 mt-1">Latest uploads from your favorite artists</p>
            </div>
            <x-home.new-releases :tracks="$newReleases" />
        </section>

        <!-- EXPLORE BY GENRE -->
        <section class="mb-8 sm:mb-10 lg:mb-12 section-spacing">
            <div class="mb-4">
                <h2 class="text-2xl sm:text-3xl font-bold text-black">🎵 Explore by Genre</h2>
                <p class="text-sm text-black/60 mt-1">Find your favorite music style</p>
            </div>
            <x-home.genres :genres="$genres" :genreCounts="$genreCounts" />
        </section>

        <!-- RECOMMENDATIONS (if trending tracks exist) -->
        @if($trendingTracks->count() > 0)
        <section class="mb-8 sm:mb-10 lg:mb-12 section-spacing">
            <div class="bg-white border border-black/10 rounded-2xl p-6 sm:p-8 shadow-sm text-center">
                <h3 class="text-xl sm:text-2xl font-bold text-black mb-3">Ready to Dive Deeper?</h3>
                <p class="text-black/70 mb-6">Explore curated playlists and recommendations tailored to your taste.</p>
                <a href="{{ route('trending') }}" class="glass-btn-primary inline-block">
                    <span>View All Recommendations</span>
                </a>
            </div>
        </section>
        @endif

    </div>
</div>
@endsection
