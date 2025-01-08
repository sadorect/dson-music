@extends('layouts.admin')

@section('title', 'Artist Analytics')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">{{ $artist->artist_name }} - Analytics</h2>
            <div class="flex space-x-4">
                <select class="border rounded px-4 py-2">
                    <option>Last 7 days</option>
                    <option>Last 30 days</option>
                    <option>Last 90 days</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm text-gray-500">Total Plays</h3>
                <p class="text-2xl font-bold">{{ number_format($artist->total_plays) }}</p>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm text-gray-500">Unique Listeners</h3>
                <p class="text-2xl font-bold">{{ number_format($artist->unique_listeners) }}</p>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm text-gray-500">Average Daily Plays</h3>
                <p class="text-2xl font-bold">{{ number_format($artist->avg_daily_plays) }}</p>
            </div>
        </div>

        <canvas id="artistChart" class="w-full"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Top Tracks</h3>
            <div class="space-y-4">
                @foreach($artist->top_tracks as $track)
                <div class="flex items-center">
                    <img src="{{ Storage::url($track->cover_art) }}" class="w-12 h-12 rounded">
                    <div class="ml-4 flex-1">
                        <h4 class="font-medium">{{ $track->title }}</h4>
                        <p class="text-sm text-gray-500">{{ number_format($track->plays_count) }} plays</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Listener Demographics</h3>
            <canvas id="demographicsChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize charts with data from backend
</script>
@endpush
@endsection
