@extends('layouts.admin')

@section('title', 'Manage Tracks')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Tracks</h2>
            <div class="flex space-x-4">
                <input type="text" placeholder="Search tracks..." class="border rounded px-4 py-2">
                <select class="border rounded px-4 py-2">
                    <option>All Genres</option>
                    <option>Pop</option>
                    <option>Hip Hop</option>
                    <option>R&B</option>
                    <option>Rock</option>
                </select>
            </div>
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
                            <img src="{{ Storage::url($track->cover_art) }}" class="w-10 h-10 rounded object-cover">
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
                    <td class="px-6 py-4 text-sm font-medium">
                        <a href="{{ route('admin.tracks.edit', $track) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
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
