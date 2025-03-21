@extends('layouts.app')

@section('title', 'GRIN Music - Stream Your Favorite Music')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="">
        <x-home.hero />
    </section>
    
    <!-- Featured Artists -->
    <section class="py-5">
        <x-home.featured-artists :artists="$featuredArtists" />
    </section>

    <!-- Trending Tracks -->
    <section class="py-5 bg-gray-50">
        <x-home.trending-tracks :trendingTracks="$trendingTracks" />
    </section>

    <!-- New Releases -->
    <section class="py-5">
        <x-home.new-releases :tracks="$newReleases" />
    </section>

    <!-- Genre Explorer -->
    <section class="py-5 bg-gray-50">
        <x-home.genres :genres="$genres" :genreCounts="$genreCounts" />
    </section>
</div>
@endsection
