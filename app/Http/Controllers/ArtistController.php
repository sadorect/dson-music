<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArtistProfile;
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
            'cover_image' => 'nullable|image|max:2048'
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

    public function dashboard()
    {
        if (!Auth::user()?->artistProfile) {
            return redirect()->route('artist.register.form')
                ->with('message', 'Please complete your artist profile first');
        }

        $artist = Auth::user()->artistProfile;
        return view('artist.dashboard', compact('artist'));
    }

    // For authenticated artist viewing their own profile

    public function show()
    {
        $artist = Auth::user()->artistProfile;

        if (!$artist) {
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

        if (!$artist) {
            abort(404);
        }

        return $this->renderPublicProfile($artist);
    }

    protected function renderPublicProfile(ArtistProfile $artist)
    {
        $artist->loadCount(['tracks', 'followers']);

        $popularTracks = $artist->tracks()
            ->with('artist')
            ->withCount('plays')
            ->orderByDesc('plays_count')
            ->take(5)
            ->get();

        $featuredTracks = $artist->tracks()
            ->with('artist')
            ->latest()
            ->take(6)
            ->get();

        $latestAlbums = $artist->albums()
            ->latest()
            ->take(6)
            ->get();

        $appearsOn = $artist->tracks()
            ->whereNotNull('album_id')
            ->with('artist')
            ->latest()
            ->take(6)
            ->get();

        $relatedArtists = ArtistProfile::where('id', '!=', $artist->id)
            ->when($artist->genre, function ($query) use ($artist) {
                $query->where('genre', $artist->genre);
            })
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
        $artists = ArtistProfile::where('is_verified', true)
            ->withCount(['tracks', 'followers'])
            ->latest()
            ->paginate(12);



        return view('artists.index', compact('artists'));
    }

    public function edit(ArtistProfile $artist)
    {
        if (!$artist) {
            return redirect()->route('artist.profile.create')
                ->with('message', 'Please create your artist profile first');
        }

        return view('artist.profile.edit', compact('artist'));
    }
}
