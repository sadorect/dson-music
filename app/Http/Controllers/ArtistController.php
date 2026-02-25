<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistController extends Controller
{
    public function showRegistrationForm()
    {
        return view('artist.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'artist_name' => 'required|unique:artist_profiles',
            'genre' => 'required',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $artistProfile = new ArtistProfile($validated);

        if ($request->hasFile('profile_image')) {
            $artistProfile->profile_image = $request->file('profile_image')->store('artist-profiles', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $artistProfile->cover_image = $request->file('cover_image')->store('artist-covers', 'public');
        }

        $request->user()->artistProfile()->save($artistProfile);

        return redirect()->route('artist.dashboard')->with('success', 'Artist profile created successfully!');
    }

    public function dashboard(CacheService $cacheService)
    {
        if (! Auth::user()?->artistProfile) {
            return redirect()->route('artist.register.form')
                ->with('message', 'Please complete your artist profile first');
        }

        $artist = Auth::user()->artistProfile;
        $stats = $cacheService->getArtistStats($artist->id);

        return view('artist.dashboard', compact('artist', 'stats'));
    }

    // For authenticated artist viewing their own profile

    public function show()
    {
        $artist = Auth::user()->artistProfile;

        if (! $artist) {
            return redirect()->route('artist.profile.create')
                ->with('message', 'Please create your artist profile first');
        }

        $artist->loadCount(['tracks', 'followers'])
            ->load(['tracks' => function ($query) {
                $query->withCount(['plays', 'likes', 'downloads']);
            }]);

        return view('artist.profile.show', compact('artist'));
    }

    // For public viewing of artist profiles via route model binding
    public function showPublicProfile(ArtistProfile $artist)
    {
        return $this->renderPublicProfile($artist);
    }

    public function showPublicBySlug(string $slug)
    {
        $artist = $this->findArtistBySlug($slug);

        if (! $artist) {
            abort(404);
        }

        return $this->renderPublicProfile($artist);
    }

    protected function renderPublicProfile(ArtistProfile $artist)
    {
        $artist->loadCount(['tracks', 'followers']);

        $popularTracks = $artist->tracks()
            ->with(['artist.user', 'album'])
            ->withCount('plays')
            ->orderByDesc('play_count')
            ->take(5)
            ->get();

        $featuredTracks = $artist->tracks()
            ->with(['artist.user', 'album'])
            ->latest()
            ->take(6)
            ->get();

        $latestAlbums = $artist->albums()
            ->with('tracks')
            ->latest()
            ->take(6)
            ->get();

        $appearsOn = $artist->tracks()
            ->whereNotNull('album_id')
            ->with(['artist.user', 'album'])
            ->latest()
            ->take(6)
            ->get();

        $relatedArtists = ArtistProfile::where('id', '!=', $artist->id)
            ->when($artist->genre, function ($query) use ($artist) {
                $query->where('genre', $artist->genre);
            })
            ->with('user')
            ->withCount('tracks')
            ->inRandomOrder()
            ->take(8)
            ->get();

        return view('artists.public.show', [
            'artist' => $artist,
            'popularTracks' => $popularTracks,
            'featuredTracks' => $featuredTracks,
            'latestAlbums' => $latestAlbums,
            'appearsOn' => $appearsOn,
            'relatedArtists' => $relatedArtists,
        ]);
    }

    protected function findArtistBySlug(string $slug): ?ArtistProfile
    {
        $normalized = \Illuminate\Support\Str::of($slug)->replace('-', ' ')->lower()->value();

        return ArtistProfile::where('custom_url', $slug)
            ->orWhereRaw('LOWER(artist_name) = ?', [$normalized])
            ->first();
    }

    public function index()
    {
        $artists = ArtistProfile::query()
            ->with('user')
            ->withCount(['tracks', 'followers'])
            ->latest()
            ->paginate(12);

        return view('artists.index', compact('artists'));
    }

    public function edit()
    {
        $artist = Auth::user()->artistProfile;

        if (! $artist) {
            return redirect()->route('artist.register.form')
                ->with('message', 'Please create your artist profile first');
        }

        return view('artist.profile.edit', compact('artist'));
    }

    public function update(Request $request)
    {
        $artist = Auth::user()->artistProfile;

        if (! $artist) {
            return redirect()->route('artist.register.form');
        }

        $validated = $request->validate([
            'artist_name'  => 'required|string|max:255|unique:artist_profiles,artist_name,' . $artist->id,
            'genre'        => 'required|string|max:100',
            'bio'          => 'nullable|string|max:2000',
            'custom_url'   => 'nullable|string|max:100|unique:artist_profiles,custom_url,' . $artist->id,
            'profile_image' => 'nullable|image|max:3072',
            'cover_image'  => 'nullable|image|max:5120',
            'social_facebook'  => 'nullable|url|max:255',
            'social_twitter'   => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_tiktok'    => 'nullable|url|max:255',
            'social_youtube'   => 'nullable|url|max:255',
        ]);

        $artist->fill([
            'artist_name' => $validated['artist_name'],
            'genre'       => $validated['genre'],
            'bio'         => $validated['bio'] ?? $artist->bio,
            'custom_url'  => $validated['custom_url'] ?? $artist->custom_url,
            'social_links' => [
                'facebook'  => $validated['social_facebook'] ?? '',
                'twitter'   => $validated['social_twitter'] ?? '',
                'instagram' => $validated['social_instagram'] ?? '',
                'tiktok'    => $validated['social_tiktok'] ?? '',
                'youtube'   => $validated['social_youtube'] ?? '',
            ],
        ]);

        if ($request->hasFile('profile_image')) {
            $artist->profile_image = $request->file('profile_image')->store('artist-profiles', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $artist->cover_image = $request->file('cover_image')->store('artist-covers', 'public');
        }

        $artist->save();

        return redirect()->route('artist.profile.edit')->with('success', 'Profile updated successfully!');
    }

    public function statistics(CacheService $cacheService)
    {
        if (! Auth::user()?->artistProfile) {
            return redirect()->route('artist.register.form')
                ->with('message', 'Please complete your artist profile first');
        }

        $artist = Auth::user()->artistProfile;
        $stats  = $cacheService->getArtistStats($artist->id);

        // Per-track breakdown
        $tracks = $artist->tracks()
            ->withCount(['plays', 'likes', 'downloads'])
            ->orderByDesc('play_count')
            ->get();

        // Monthly plays for the last 6 months
        $monthlyPlays = \DB::table('play_histories')
            ->whereIn('track_id', $artist->tracks()->pluck('id'))
            ->where('played_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(played_at, '%Y-%m') as month, COUNT(*) as plays")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Build a full 6-month array (fill gaps with 0)
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $months[$key] = $monthlyPlays->get($key)?->plays ?? 0;
        }

        return view('artist.statistics', compact('artist', 'stats', 'tracks', 'months'));
    }
}
