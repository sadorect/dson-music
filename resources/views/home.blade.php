@extends('layouts.app')

@section('title', 'DSON Music - Stream Your Favorite Music')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container mx-auto px-4">
            <h1 class="text-5xl font-bold mb-6 animate-fade-in">Your Music, Your Way</h1>
            <p class="text-xl mb-8 opacity-90">Stream millions of songs with DSON Music</p>
            <a href="{{ route('register') }}" class="dson-btn text-lg hover:scale-105 transform transition">
                Start Listening
            </a>
        </div>
    </section>
    

    <!-- Featured Playlists -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">Featured Playlists</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="playlist-card p-6">
                <img src="https://images.pexels.com/photos/1626481/pexels-photo-1626481.jpeg" alt="Hip Hop Playlist" class="w-full h-48 object-cover rounded-lg mb-4">
                <h3 class="text-xl font-bold mb-2">Hip Hop Essentials</h3>
                <p class="text-gray-600">Latest beats and classic tracks</p>
            </div>
            <div class="playlist-card p-6">
                <img src="https://images.pexels.com/photos/1389429/pexels-photo-1389429.jpeg" alt="Rock Playlist" class="w-full h-48 object-cover rounded-lg mb-4">
                <h3 class="text-xl font-bold mb-2">Rock Legends</h3>
                <p class="text-gray-600">Ultimate rock collection</p>
            </div>
            <div class="playlist-card p-6">
                <img src="https://images.pexels.com/photos/1763075/pexels-photo-1763075.jpeg" alt="Electronic Playlist" class="w-full h-48 object-cover rounded-lg mb-4">
                <h3 class="text-xl font-bold mb-2">Electronic Mix</h3>
                <p class="text-gray-600">Best EDM and dance hits</p>
            </div>
        </div>
        
    </div></section>

<!-- New Releases -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">New Releases</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <img src="path/to/album1.jpg" alt="Album" class="w-full aspect-square object-cover rounded-lg mb-3">
                <h4 class="font-semibold">Song Title</h4>
                <p class="text-gray-600 text-sm">Artist Name</p>
            </div>
            <!-- Repeat for other tracks -->
        </div>
    </div>
</section>


    @include('components.player')
</div>
@endsection
