<?php

use App\Models\Donation;
use App\Models\PlayHistory;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->isArtist(), 403);

        if (! auth()->user()->artistProfile) {
            $this->redirect(route('artist.setup'), navigate: true);
        }
    }

    public function with(): array
    {
        $profile = auth()->user()->artistProfile;

        if (! $profile) {
            return [
                'profile' => null,
                'overview' => [],
                'momentum' => [],
                'topTracks' => collect(),
                'recentTracks' => collect(),
                'recentSupport' => collect(),
                'genreBreakdown' => collect(),
                'moodBreakdown' => collect(),
            ];
        }

        $trackIds = $profile->tracks()->pluck('id');
        $publishedTracks = $profile->tracks()->where('is_published', true);

        $topTracks = $profile->tracks()
            ->with('genre')
            ->withCount('likedByUsers as likes_count')
            ->orderByDesc('play_count')
            ->orderByDesc('downloads_count')
            ->take(5)
            ->get();

        $recentTracks = $profile->tracks()
            ->with('genre')
            ->latest()
            ->take(5)
            ->get();

        $recentSupport = Donation::query()
            ->with(['user', 'track'])
            ->where('artist_profile_id', $profile->id)
            ->where(function ($query): void {
                $query->whereNull('status')->orWhere('status', 'completed');
            })
            ->latest()
            ->take(5)
            ->get();

        $genreBreakdown = $publishedTracks
            ->with('genre')
            ->get()
            ->filter(fn ($track) => $track->genre)
            ->groupBy('genre_id')
            ->map(fn ($group) => [
                'genre' => $group->first()->genre,
                'count' => $group->count(),
                'plays' => $group->sum('play_count'),
            ])
            ->sortByDesc('plays')
            ->take(4)
            ->values();

        $moodBreakdown = $publishedTracks
            ->with('genre')
            ->get()
            ->filter(fn ($track) => filled($track->effective_mood))
            ->groupBy(fn ($track) => $track->effective_mood)
            ->map(fn ($group, $mood) => [
                'mood' => $mood,
                'label' => $group->first()->mood_label ?? str($mood)->replace('-', ' ')->replace('_', ' ')->title()->value(),
                'count' => $group->count(),
                'plays' => $group->sum('play_count'),
                'downloads' => $group->sum('downloads_count'),
            ])
            ->sortByDesc('plays')
            ->take(4)
            ->values();

        return [
            'profile' => $profile,
            'overview' => [
                'totalTracks' => $publishedTracks->count(),
                'totalPlays' => $profile->tracks()->sum('play_count'),
                'totalDownloads' => $profile->tracks()->sum('downloads_count'),
                'totalDonations' => $profile->donations()->sum('amount'),
                'followers' => $profile->followers()->count(),
                'avgPlaysPerTrack' => $publishedTracks->count() > 0
                    ? round($profile->tracks()->sum('play_count') / max(1, $publishedTracks->count()))
                    : 0,
            ],
            'momentum' => [
                'plays30d' => $trackIds->isEmpty()
                    ? 0
                    : PlayHistory::query()->whereIn('track_id', $trackIds)->where('created_at', '>=', now()->subDays(30))->count(),
                'plays7d' => $trackIds->isEmpty()
                    ? 0
                    : PlayHistory::query()->whereIn('track_id', $trackIds)->where('created_at', '>=', now()->subDays(7))->count(),
                'donations30d' => Donation::query()
                    ->where('artist_profile_id', $profile->id)
                    ->where(function ($query): void {
                        $query->whereNull('status')->orWhere('status', 'completed');
                    })
                    ->where('created_at', '>=', now()->subDays(30))
                    ->sum('amount'),
                'supporters30d' => Donation::query()
                    ->where('artist_profile_id', $profile->id)
                    ->where(function ($query): void {
                        $query->whereNull('status')->orWhere('status', 'completed');
                    })
                    ->where('created_at', '>=', now()->subDays(30))
                    ->distinct('user_id')
                    ->count('user_id'),
                'uploadedThisMonth' => $profile->tracks()->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'topTracks' => $topTracks,
            'recentTracks' => $recentTracks,
            'recentSupport' => $recentSupport,
            'genreBreakdown' => $genreBreakdown,
            'moodBreakdown' => $moodBreakdown,
        ];
    }
};
?>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-8">

    @if(!$profile)
        <div class="glass-card rounded-2xl p-8 text-center">
            <p class="text-gray-600">Your artist profile is being set up. Please check back shortly.</p>
        </div>
    @else
        <section class="glass-card rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Artist Dashboard</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-gray-900 sm:text-4xl">{{ $profile->display_name }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 sm:text-base">Your performance snapshot now includes audience momentum, top tracks, support activity, and genre footprint.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('artist.tracks') }}" wire:navigate
                       class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                        Manage Tracks
                    </a>
                    <a href="{{ route('artist.upload-track') }}" wire:navigate
                       class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                        Upload Track
                    </a>
                    <a href="{{ route('artist.create-album') }}" wire:navigate
                       class="rounded-full border border-white/70 bg-white/80 px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-white">
                        New Album
                    </a>
                </div>
            </div>
        </section>

        @if(!$profile->is_approved)
            <div class="glass-card rounded-2xl p-4 border border-yellow-200 bg-yellow-50/60">
                <p class="text-sm text-yellow-700 font-medium">Your artist profile is pending admin approval. You can still upload tracks, but they will not be publicly visible until approved.</p>
            </div>
        @endif

        <div class="grid grid-cols-2 xl:grid-cols-6 gap-4">
            @foreach([
                ['label' => 'Published Tracks', 'value' => number_format($overview['totalTracks'])],
                ['label' => 'Total Plays', 'value' => number_format($overview['totalPlays'])],
                ['label' => 'Downloads', 'value' => number_format($overview['totalDownloads'])],
                ['label' => 'Followers', 'value' => number_format($overview['followers'])],
                ['label' => 'Donations', 'value' => '$' . number_format($overview['totalDonations'], 2)],
                ['label' => 'Avg Plays / Track', 'value' => number_format($overview['avgPlaysPerTrack'])],
            ] as $stat)
                <div class="glass-card rounded-2xl p-5">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1.05fr),minmax(260px,0.95fr)]">
            <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Momentum</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">How the last 30 days are moving</h2>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach([
                        ['label' => 'Plays in 30 Days', 'value' => number_format($momentum['plays30d'])],
                        ['label' => 'Plays in 7 Days', 'value' => number_format($momentum['plays7d'])],
                        ['label' => 'Supporters in 30 Days', 'value' => number_format($momentum['supporters30d'])],
                        ['label' => 'Donations in 30 Days', 'value' => '$' . number_format($momentum['donations30d'], 2)],
                        ['label' => 'Uploaded This Month', 'value' => number_format($momentum['uploadedThisMonth'])],
                    ] as $metric)
                        <div class="rounded-[1.4rem] border border-white/60 bg-white/80 px-4 py-4 shadow-sm">
                            <p class="text-xl font-black text-gray-900">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Genre Footprint</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Where your strongest traction sits</h2>
                    <div class="mt-5 space-y-3">
                        @forelse($genreBreakdown as $item)
                            <div class="rounded-[1.4rem] border border-white/50 bg-white/70 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $item['genre']->name }}</p>
                                    <span class="text-xs font-semibold text-gray-400">{{ $item['count'] }} tracks</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">{{ number_format($item['plays']) }} total plays</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Publish more genre-tagged tracks to see where your catalog is performing best.</p>
                        @endforelse
                    </div>
                </div>

                <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Mood Performance</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Which vibes are landing best</h2>
                    <div class="mt-5 space-y-3">
                        @forelse($moodBreakdown as $item)
                            <div class="rounded-[1.4rem] border border-white/50 bg-white/70 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item['label'] }}</p>
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">{{ $item['count'] }} tracks</span>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-400">{{ number_format($item['plays']) }} plays</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">{{ number_format($item['downloads']) }} downloads across this mood lane</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Add moods to more tracks to see which listening lanes are performing best.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-8 xl:grid-cols-[minmax(0,1fr),minmax(0,1fr)]">
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Top Performing Tracks</h2>
                    <a href="{{ route('artist.tracks') }}" wire:navigate class="text-sm text-primary hover:underline">View all &rarr;</a>
                </div>
                @if($topTracks->isEmpty())
                    <div class="px-6 py-10 text-center text-gray-500 text-sm">No performance data yet.</div>
                @else
                    <ul class="divide-y divide-white/30">
                        @foreach($topTracks as $track)
                            <li class="flex items-center gap-4 px-6 py-3 hover:bg-white/40 transition">
                                <div class="w-11 h-11 rounded-xl bg-gray-200 overflow-hidden shrink-0">
                                    @if($track->getCoverUrl())
                                        <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="{{ $track->cover_alt }}">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $track->title }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                        <span>{{ $track->genre?->name ?? 'Open format' }}</span>
                                        @if($track->mood_label)
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                        @endif
                                        <span>{{ $track->formatted_duration }}</span>
                                    </div>
                                </div>
                                <div class="hidden sm:grid sm:grid-cols-3 sm:gap-4 text-xs text-gray-500 shrink-0">
                                    <span>{{ number_format($track->play_count) }} plays</span>
                                    <span>{{ number_format($track->downloads_count) }} downloads</span>
                                    <span>{{ number_format($track->likes_count) }} likes</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Recent Support</h2>
                    <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Latest donations</span>
                </div>
                @if($recentSupport->isEmpty())
                    <div class="px-6 py-10 text-center text-gray-500 text-sm">No support activity yet.</div>
                @else
                    <ul class="divide-y divide-white/30">
                        @foreach($recentSupport as $donation)
                            <li class="flex items-center justify-between gap-4 px-6 py-3 hover:bg-white/40 transition">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $donation->user?->name ?? 'Anonymous supporter' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $donation->track?->title ?? 'Direct support' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-semibold text-gray-900">${{ number_format($donation->amount, 2) }}</p>
                                    <p class="text-xs text-gray-400">{{ $donation->created_at->diffForHumans() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Recent Tracks</h2>
                <a href="{{ route('artist.tracks') }}" wire:navigate class="text-sm text-primary hover:underline">View all &rarr;</a>
            </div>
            @if($recentTracks->isEmpty())
                <div class="px-6 py-10 text-center text-gray-500 text-sm">
                    No tracks yet. <a href="{{ route('artist.upload-track') }}" wire:navigate class="text-primary hover:underline">Upload your first track &rarr;</a>
                </div>
            @else
                <ul class="divide-y divide-white/30">
                    @foreach($recentTracks as $track)
                        <li class="flex items-center gap-4 px-6 py-3 hover:bg-white/40 transition">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                                @if($track->getCoverUrl())
                                    <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="{{ $track->cover_alt }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $track->title }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                    <span>{{ $track->genre?->name ?? 'Open format' }}</span>
                                    @if($track->mood_label)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                    @endif
                                    <span>{{ $track->formatted_duration }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-gray-500 shrink-0">
                                <span>{{ number_format($track->play_count) }} plays</span>
                                <span class="{{ $track->is_published ? 'text-green-600' : 'text-yellow-600' }} font-medium">
                                    {{ $track->is_published ? 'Live' : 'Draft' }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
