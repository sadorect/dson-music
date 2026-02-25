@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-orange-500">
            <h3 class="text-gray-500 text-sm font-medium">Total Plays</h3>
            <p class="text-3xl font-bold mt-1">{{ number_format($stats['total_plays']) }}</p>
            <div class="mt-2 text-green-600 text-sm font-medium">
                +{{ number_format($stats['monthly_plays']) }} this month
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
            <h3 class="text-gray-500 text-sm font-medium">Active Artists</h3>
            <p class="text-3xl font-bold mt-1">{{ number_format($stats['popular_tracks']->count() > 0 ? \App\Models\ArtistProfile::count() : 0) }}</p>
            <p class="mt-2 text-gray-400 text-sm">Registered artists</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-purple-500">
            <h3 class="text-gray-500 text-sm font-medium">Top Track Plays</h3>
            <p class="text-3xl font-bold mt-1">{{ number_format($stats['popular_tracks']->first()?->plays_count ?? 0) }}</p>
            <p class="mt-2 text-gray-400 text-sm">{{ $stats['popular_tracks']->first()?->title ?? 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
            <h3 class="text-gray-500 text-sm font-medium">Active Listeners</h3>
            <p class="text-3xl font-bold mt-1">{{ number_format(count($stats['active_users'])) }}</p>
            <p class="mt-2 text-gray-400 text-sm">Most active users</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Plays Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Plays Timeline</h3>
            <canvas id="playsChart"></canvas>
        </div>

        <!-- Popular Tracks -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Top Tracks</h3>
            <div class="space-y-4">
                @foreach($stats['popular_tracks'] as $track)
                <div class="flex items-center">
                    <img src="{{ Storage::url($track->cover_art) }}" class="w-12 h-12 rounded">
                    <div class="ml-4 flex-1">
                        <h4 class="font-medium">{{ $track->title }}</h4>
                        <p class="text-sm text-gray-500">{{ $track->artist->artist_name }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold">{{ number_format($track->plays_count) }}</span>
                        <span class="text-gray-500 text-sm">plays</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Active Users -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Most Active Users</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($stats['active_users'] as $user)
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-lg font-medium">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="font-medium">{{ $user->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold">{{ number_format($user->plays_count) }}</span>
                        <span class="text-gray-500 text-sm">plays</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('playsChart').getContext('2d');
const playsData = @json($stats['plays_by_day']);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: playsData.map(item => item.date),
        datasets: [{
            label: 'Plays',
            data: playsData.map(item => item.count),
            borderColor: 'rgb(59, 130, 246)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
@endsection
