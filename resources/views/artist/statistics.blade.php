@extends('layouts.artist')

@section('title', 'Statistics — ' . $artist->artist_name)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">

    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Statistics</h1>
            <p class="text-gray-500 text-sm mt-1">Performance overview for <span class="font-medium text-orange-600">{{ $artist->artist_name }}</span></p>
        </div>
        <span class="text-xs text-gray-400 bg-orange-50 border border-orange-100 px-3 py-1.5 rounded-full">Last updated: {{ now()->format('d M Y, H:i') }}</span>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
        <div class="bg-gradient-to-br from-[#2b1306] to-[#140b05] p-5 rounded-xl shadow border border-orange-900/40 text-white">
            <p class="text-xs text-orange-300/80 uppercase tracking-wide">Total Plays</p>
            <p class="text-3xl font-bold text-orange-50 mt-1">{{ number_format($stats['total_plays']) }}</p>
            <svg class="w-8 h-8 text-orange-500/30 mt-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
            </svg>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border border-orange-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Followers</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['followers']) }}</p>
            <svg class="w-8 h-8 text-orange-300 mt-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
            </svg>
        </div>

        <div class="bg-orange-50 p-5 rounded-xl shadow border border-orange-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Likes</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_likes']) }}</p>
            <svg class="w-8 h-8 text-orange-400/40 mt-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
            </svg>
        </div>

        <div class="bg-white p-5 rounded-xl shadow border border-orange-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Downloads</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_downloads']) }}</p>
            <svg class="w-8 h-8 text-orange-300 mt-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </div>

    <!-- Monthly Plays Chart -->
    <div class="bg-white rounded-xl shadow border border-orange-100 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Monthly Plays — Last 6 Months</h2>
        @php
            $maxPlays = max(1, max($months->values()->toArray()));
        @endphp
        <div class="flex items-end gap-3 h-40">
            @foreach($months as $month => $plays)
                @php $pct = round(($plays / $maxPlays) * 100); @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    <span class="text-xs font-medium text-orange-600">{{ $plays > 0 ? number_format($plays) : '' }}</span>
                    <div class="w-full rounded-t-md transition-all duration-500"
                         style="height: {{ max(4, $pct) }}%; background: linear-gradient(to top, #ea580c, #fb923c);"
                         title="{{ $month }}: {{ number_format($plays) }} plays"></div>
                    <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M') }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Per-Track Breakdown -->
    <div class="bg-white rounded-xl shadow border border-orange-100 overflow-hidden">
        <div class="p-5 border-b border-orange-50 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Track Performance</h2>
            <span class="text-xs text-gray-400">{{ $tracks->count() }} {{ Str::plural('track', $tracks->count()) }}</span>
        </div>

        @if($tracks->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">
                No tracks uploaded yet. <a href="{{ route('artist.tracks.create') }}" class="text-orange-500 hover:underline">Upload your first track &rarr;</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-orange-50 text-left">
                            <th class="px-5 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">#</th>
                            <th class="px-5 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Track</th>
                            <th class="px-5 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide text-right">Plays</th>
                            <th class="px-5 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide text-right">Likes</th>
                            <th class="px-5 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide text-right">Downloads</th>
                            <th class="px-5 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-50">
                        @foreach($tracks as $i => $track)
                            <tr class="hover:bg-orange-50/50 transition-colors">
                                <td class="px-5 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('artist.tracks.show', $track) }}" class="text-gray-900 font-medium hover:text-orange-600 transition-colors">{{ $track->title }}</a>
                                    @if($track->album)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $track->album->title }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="font-semibold text-gray-900">{{ number_format($track->plays_count) }}</span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ number_format($track->likes_count) }}</td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ number_format($track->downloads_count) }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $status = $track->status ?? $track->approval_status ?? 'unknown';
                                        $badge = match($status) {
                                            'approved', 'published', 'active' => 'bg-green-100 text-green-700',
                                            'pending'                          => 'bg-orange-100 text-orange-700',
                                            'draft'                            => 'bg-gray-100 text-gray-600',
                                            'rejected'                         => 'bg-red-100 text-red-700',
                                            default                            => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
