@extends('layouts.app')

@section('title', 'GRIN Music - Stream Your Favorite Music')

@section('content')
<div class="flex p-3 gap-4 h-full">
    <div class="w-3/12 bg-white/[5%] rounded-lg ">

    </div>


    <div class="w-9/12 bg-white/5 p-6 rounded-lg overflow-y-scroll h-full">
        <!-- Hero Section -->
    <section class="">
        <x-home.trending />
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
</div>
@endsection
