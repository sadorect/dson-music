@extends('layouts.app')

@section('title', 'GRIN Music - Stream Your Favorite Music')

@section('content')
<div>
    <!-- Hero Section -->
    <section class="">
        <x-home.trending />
    </section>

    <!-- Featured Artists -->
    <section class="my-8">
        <x-home.featured-artists :artists="$featuredArtists" />
    </section>


    <section class="">
        <x-home.popular-artists />
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