<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        use Illuminate\Support\Str;

        $segments = request()->segments();
        $primarySegment = $segments[0] ?? null;
        $secondarySegment = $segments[1] ?? null;

        $resourceMessages = [
            'track' => [
                'title' => 'Track not found',
                'description' => "The track you're trying to reach isn't available. Let's get you back to the music.",
            ],
            'artist' => [
                'title' => 'Artist not found',
                'description' => "That artist page isn't available right now. Try discovering someone new from the browse page.",
            ],
            'playlist' => [
                'title' => 'Playlist not found',
                'description' => "We couldn't find that playlist. You can still explore other collections and fresh music.",
            ],
            'album' => [
                'title' => 'Album not found',
                'description' => "That album isn't available here anymore. Let's point you to something else worth a listen.",
            ],
        ];

        $resourceType = match (true) {
            $primarySegment === 'track' || in_array('tracks', $segments, true) => 'track',
            $primarySegment === 'playlist' || in_array('playlists', $segments, true) => 'playlist',
            $primarySegment === 'album' || in_array('albums', $segments, true) => 'album',
            $primarySegment === 'artist' && ! in_array($secondarySegment, ['setup', 'tracks', 'albums'], true) => 'artist',
            default => null,
        };

        if ($resourceType !== null) {
            $errorTitle = $resourceMessages[$resourceType]['title'];
            $errorDescription = $resourceMessages[$resourceType]['description'];
        } else {
            $fallbackSegment = collect(array_reverse($segments))
                ->first(fn (string $segment): bool => ! is_numeric($segment) && ! in_array($segment, ['create', 'edit'], true));

            $pageLabel = $fallbackSegment
                ? Str::of($fallbackSegment)->replace(['-', '_'], ' ')->title()->toString()
                : 'Page';

            $errorTitle = $pageLabel === 'Page'
                ? 'Page not found'
                : "{$pageLabel} page not found";

            $errorDescription = $pageLabel === 'Page'
                ? "This page must have been remixed out of existence. Let's get you back to the music."
                : "We couldn't find the {$pageLabel} page you were trying to reach. Let's get you back to the music.";
        }
    @endphp
    <title>{{ $errorTitle }} - {{ $siteTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased" style="background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #ffe4e6 60%, #fef3c7 100%);">

    {{-- Decorative blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-40 -right-32 h-[500px] w-[500px] rounded-full opacity-20"
             style="background: radial-gradient(circle, #f472b6 0%, transparent 70%);"></div>
        <div class="absolute -bottom-32 -left-24 h-[400px] w-[400px] rounded-full opacity-20"
             style="background: radial-gradient(circle, #fb7185 0%, transparent 70%);"></div>
        <div class="absolute left-1/2 top-1/2 h-[600px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-10"
             style="background: radial-gradient(circle, #fbbf24 0%, transparent 60%);"></div>
    </div>

    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 text-center">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="group mb-10 flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg transition group-hover:scale-105">
                <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/>
                </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-gray-800">{{ $siteName }}</span>
        </a>

        {{-- Glass card --}}
        <div class="glass-panel w-full max-w-md rounded-3xl p-10 shadow-xl">

            {{-- Animated note --}}
            <div class="mb-6 flex justify-center">
                <div class="relative">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                        <svg class="h-10 w-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                        </svg>
                    </div>
                    <span class="absolute -right-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white shadow">
                        404
                    </span>
                </div>
            </div>

            <h1 class="mb-2 text-3xl font-bold text-gray-800">{{ $errorTitle }}</h1>
            <p class="mb-8 text-sm leading-relaxed text-gray-500">
                {{ $errorDescription }}
            </p>

            {{-- Actions --}}
            <div class="space-y-3">
                <a href="{{ route('home') }}"
                   class="glass-btn-primary glass-btn-primary-hover flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-semibold transition">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Go home
                </a>

                <a href="{{ route('browse') }}"
                   class="flex w-full items-center justify-center gap-2 rounded-2xl border border-white/50 bg-white/60 px-6 py-3 text-sm font-semibold text-gray-600 transition hover:bg-white/80">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Browse music
                </a>
            </div>
        </div>

        <p class="mt-8 text-xs text-gray-400">
            Error 404 &mdash; Page not found
        </p>
    </div>

</body>
</html>
