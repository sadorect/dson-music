@extends('layouts.admin')

@section('title', 'Manage Tracks')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="text-xl font-semibold">Tracks</h2>
            <form action="{{ route('admin.tracks.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search tracks…"
                       class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                       onkeydown="if(event.key==='Enter')this.form.submit()">
                <select name="genre" onchange="this.form.submit()" class="border rounded-lg px-4 py-2 text-sm">
                    <option value="">All Genres</option>
                    @foreach(['pop','hip-hop','r&b','rock','jazz','gospel','classical','afrobeats','reggae'] as $g)
                        <option value="{{ $g }}" {{ request('genre') === $g ? 'selected' : '' }}>{{ ucwords($g) }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" class="border rounded-lg px-4 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="private" {{ request('status') === 'private' ? 'selected' : '' }}>Private</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm hover:bg-orange-600 transition-colors">
                    Search
                </button>
                @if(request()->hasAny(['search','genre','status']))
                    <a href="{{ route('admin.tracks.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-orange-600">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Artist</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Genre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plays</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($tracks as $track)
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <img src="{{ $track->cover_art ? Storage::url($track->cover_art) : '/default-cover.jpg' }}" class="w-10 h-10 rounded object-cover">
                            <span class="ml-3">{{ $track->title }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">{{ $track->artist->artist_name }}</td>
                    <td class="px-6 py-4">{{ $track->genre }}</td>
                    <td class="px-6 py-4">{{ number_format($track->play_count) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $track->status === 'published' ? 'bg-green-100 text-green-800' : 
                               ($track->status === 'private' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($track->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.tracks.show', $track) }}" class="text-gray-600 hover:text-gray-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.tracks.review.show', $track) }}" class="text-indigo-600 hover:text-indigo-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </a>
                            <form action="{{ route('admin.tracks.destroy', $track) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this track?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4">
        {{ $tracks->links() }}
    </div>
</div>
@endsection
