@extends('layouts.admin')

@section('title', 'Manage Artists')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Artists</h2>
            <form action="{{ route('admin.artists.index') }}" method="GET" class="flex space-x-4">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search artists..." 
                       class="border rounded px-4 py-2">
                       
                <select name="status" onchange="this.form.submit()" class="border rounded px-4 py-2">
                    <option value="">All Status</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                </select>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Artist</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Genre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tracks</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($artists as $artist)
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <img src="{{ Storage::url($artist->profile_image) }}" 
                                 alt="{{ $artist->artist_name }}" 
                                 class="w-10 h-10 rounded-full object-cover">
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $artist->artist_name }}</div>
                                <div class="text-gray-500">{{ $artist->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">{{ $artist->genre }}</td>
                    <td class="px-6 py-4">{{ $artist->tracks_count }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $artist->is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $artist->is_verified ? 'Verified' : 'Unverified' }}
                        </span>
                    </td>
                    <!-- Add this in the Actions column -->
                  <td class="px-6 py-4 text-sm font-medium">
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.artists.edit', $artist) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        
                        @if(!$artist->is_verified)
                            <form action="{{ route('admin.artists.verify', $artist) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900">Verify</button>
                            </form>
                        @else
                            <form action="{{ route('admin.artists.unverify', $artist) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900">Unverify</button>
                            </form>
                        @endif
                    </div>
                  </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4">
        {{ $artists->links() }}
    </div>
</div>
@endsection
