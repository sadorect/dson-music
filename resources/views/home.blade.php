@extends('layouts.app')

@section('title', 'GRIN Music - Stream Your Favorite Music')

@section('content')
<div class="flex p-3 gap-8 h-full">
    <div class="w-3/12 bg-white/[5%] rounded-lg p-6 flex flex-col gap-4">

    
        <h1 class="text-white font-semibold text-lg">Your Library</h1>   
        <div class="bg-black/10 p-6 rounded-lg">
            <h1 class="text-white font-semibold text-lg">Create your first playlist</h1>
            <p class="text-gray-400">Start creating your first playlist</p>
            <button class="bg-white my-2 text-black px-4 py-2 rounded-full">Create Playlist</button>
        </div>

        <div class="bg-black/10 p-6 rounded-lg">
            <h1 class="text-white font-semibold text-lg">Let's find some podcasts to follow</h1>
            <p class="text-gray-400">We'll keep you updated on the latest episodes</p>
            <button class="bg-white my-2 text-black px-4 py-2 rounded-full">Find Podcasts</button>
        </div>

    </div>


    <div class="w-9/12 bg-white/5 p-6 rounded-lg overflow-y-scroll h-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
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
</div>
@endsection
