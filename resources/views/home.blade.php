@extends('layouts.app')

@section('title', 'GRIN Music - Stream Your Favorite Music')

@section('content')
<div class="p-6">
    <!-- Hero Section -->
    <section class="">
        <x-home.trending :tracks="$trendingTracks" title="Trending Songs" />
    </section>

    <!-- Featured Artists -->
    <section class="my-8">
        <x-home.featured-artists title="Popular Albums" :artists="$featuredArtists" />
    </section>


    <section class="">
        <x-home.popular-artists title="Popular Artists" />
    </section>

    <!-- Trending Tracks -->
    <section class="p-6 bg-black/10 rounded-lg">
        <x-home.trending-tracks :trendingTracks="$trendingTracks" />
    </section>

    <!-- New Releases -->
    <section class="my-8">
        <x-home.new-releases :tracks="$newReleases" />
    </section>

    <!-- Genre Explorer -->
    <section class="my-8">
        <x-home.genres :genres="$genres" :genreCounts="$genreCounts" />
    </section>


   
</div>
@endsection