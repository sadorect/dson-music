@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">My Releases</h1>
        <a href="{{ route('artist.albums.create') }}" class="dson-btn flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Release
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse ($albums as $album)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105">
                <div class="relative pb-[100%]">
                    <img src="{{ Storage::url($album->cover_art) }}" 
                         alt="{{ $album->title }}" 
                         class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent">
                        <div class="absolute bottom-4 left-4">
                            <span class="px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">
                                {{ ucfirst($album->type) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="font-bold text-xl mb-2">{{ $album->title }}</h3>
                    <div class="flex items-center text-gray-600 text-sm mb-4">
                        <span>{{ $album->tracks->count() }} tracks</span>
                        <span class="mx-2">•</span>
                        <span>{{ $album->release_date->format('M Y') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium px-2 py-1 rounded {{ $album->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($album->status) }}
                        </span>
                        <div class="flex space-x-2">
                            <a href="{{ route('artist.albums.edit', $album) }}" class="text-gray-600 hover:text-gray-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="{{ route('artist.albums.show', $album) }}" class="text-gray-600 hover:text-gray-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <form action="{{ route('artist.albums.destroy', $album) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this album?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No releases yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new release.</p>
                    <div class="mt-6">
                        <a href="{{ route('artist.albums.create') }}" class="dson-btn">
                            Create new release
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $albums->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const albums = document.querySelectorAll('.album-card');
        albums.forEach(album => {
            album.addEventListener('mouseenter', () => {
                album.querySelector('.album-actions').classList.remove('opacity-0');
            });
            album.addEventListener('mouseleave', () => {
                album.querySelector('.album-actions').classList.add('opacity-0');
            });
        });
    });
</script>
@endpush
@endsection
