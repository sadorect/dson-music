@extends('layouts.app')

@section('title', 'GRIN Music - Stream Your Favorite Music')

@section('content')
<div class="p-3 sm:p-4 lg:p-6">
    
    <!-- Hero Section -->
    <section class="mb-6 sm:mb-8">
        <x-home.trending :tracks="$trendingTracks" title="Trending Songs" />
    </section>

    <!-- Featured Artists -->
    <section class="mb-6 sm:mb-8">
        <x-home.featured-artists title="Popular Albums" :artists="$featuredArtists" />
    </section>

    <section class="mb-6 sm:mb-8">
        <x-home.popular-artists title="Popular Artists" :artists="$featuredArtists" />
    </section>

    <!-- Trending Tracks -->
    <section class="p-4 sm:p-6 bg-black/10 rounded-lg mb-6 sm:mb-8">
        <x-home.trending-tracks :trendingTracks="$trendingTracks" />
    </section>

    <!-- New Releases -->
    <section class="mb-6 sm:mb-8">
        <x-home.new-releases :tracks="$newReleases" />
    </section>

    <!-- Genre Explorer -->
    <section class="mb-6 sm:mb-8">
        <x-home.genres :genres="$genres" :genreCounts="$genreCounts" />
    </section>


   
</div>
@endsection