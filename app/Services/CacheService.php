<?php

namespace App\Services;

use App\Models\ArtistProfile;
use App\Models\Track;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    /**
     * Cache duration constants (in seconds)
     */
    const TRENDING_TTL = 3600; // 1 hour

    const GENRE_COUNTS_TTL = 7200; // 2 hours

    const FEATURED_ARTISTS_TTL = 3600; // 1 hour

    const NEW_RELEASES_TTL = 1800; // 30 minutes

    const ANALYTICS_TTL = 86400; // 24 hours

    /**
     * Get trending tracks with caching
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrendingTracks($limit = 10)
    {
        return Cache::remember('trending_tracks_'.$limit, self::TRENDING_TTL, function () use ($limit) {
            return Track::where('status', 'published')
                ->with(['artist.user', 'album'])
                ->orderBy('play_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get genre counts with caching
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGenreCounts()
    {
        return Cache::remember('genre_counts', self::GENRE_COUNTS_TTL, function () {
            return Track::where('status', 'published')
                ->select('genre')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('genre')
                ->orderBy('count', 'desc')
                ->pluck('count', 'genre');
        });
    }

    /**
     * Get featured/verified artists with caching
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedArtists($limit = 12)
    {
        return Cache::remember('featured_artists_'.$limit, self::FEATURED_ARTISTS_TTL, function () use ($limit) {
            return ArtistProfile::where('is_verified', true)
                ->with('user')
                ->withCount(['tracks' => function ($query) {
                    $query->where('status', 'published');
                }])
                ->orderBy('tracks_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get new releases with caching
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNewReleases($limit = 12)
    {
        return Cache::remember('new_releases_'.$limit, self::NEW_RELEASES_TTL, function () use ($limit) {
            return Track::where('status', 'published')
                ->with(['artist.user', 'album'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get homepage statistics with caching
     *
     * @return array
     */
    public function getHomeStats()
    {
        return Cache::remember('home_stats', self::ANALYTICS_TTL, function () {
            return [
                'total_tracks' => Track::where('status', 'published')->count(),
                'total_artists' => ArtistProfile::where('is_verified', true)->count(),
                'total_plays' => DB::table('play_histories')->count(),
                'total_users' => DB::table('users')->count(),
            ];
        });
    }

    /**
     * Get popular genres for homepage
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getPopularGenres($limit = 6)
    {
        return Cache::remember('popular_genres_'.$limit, self::GENRE_COUNTS_TTL, function () use ($limit) {
            return Track::where('status', 'published')
                ->select('genre')
                ->selectRaw('COUNT(*) as track_count')
                ->selectRaw('SUM(play_count) as total_plays')
                ->groupBy('genre')
                ->orderBy('total_plays', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get artist dashboard stats with caching
     *
     * @param  int  $artistId
     * @return array
     */
    public function getArtistStats($artistId)
    {
        return Cache::remember('artist_stats_'.$artistId, self::ANALYTICS_TTL, function () use ($artistId) {
            $tracks = Track::where('artist_id', $artistId)->pluck('id');

            return [
                'total_tracks' => $tracks->count(),
                'total_plays' => DB::table('play_histories')->whereIn('track_id', $tracks)->count(),
                'total_likes' => DB::table('likes')
                    ->where('likeable_type', Track::class)
                    ->whereIn('likeable_id', $tracks)
                    ->count(),
                'total_downloads' => DB::table('downloads')->whereIn('track_id', $tracks)->count(),
                'followers' => DB::table('follows')->where('artist_profile_id', $artistId)->count(),
            ];
        });
    }

    /**
     * Clear all cache
     *
     * @return void
     */
    public function clearAll()
    {
        Cache::tags(['trending', 'genres', 'artists', 'releases', 'stats'])->flush();
    }

    /**
     * Clear trending tracks cache
     *
     * @return void
     */
    public function clearTrending()
    {
        Cache::forget('trending_tracks_10');
        Cache::forget('trending_tracks_20');
        Cache::forget('trending_tracks_50');
    }

    /**
     * Clear genre counts cache
     *
     * @return void
     */
    public function clearGenreCounts()
    {
        Cache::forget('genre_counts');
        Cache::forget('popular_genres_6');
        Cache::forget('popular_genres_12');
    }

    /**
     * Clear featured artists cache
     *
     * @return void
     */
    public function clearFeaturedArtists()
    {
        Cache::forget('featured_artists_12');
        Cache::forget('featured_artists_20');
    }

    /**
     * Clear new releases cache
     *
     * @return void
     */
    public function clearNewReleases()
    {
        Cache::forget('new_releases_12');
        Cache::forget('new_releases_20');
    }

    /**
     * Clear artist-specific cache
     *
     * @param  int  $artistId
     * @return void
     */
    public function clearArtistCache($artistId)
    {
        Cache::forget('artist_stats_'.$artistId);
    }

    /**
     * Clear home stats cache
     *
     * @return void
     */
    public function clearHomeStats()
    {
        Cache::forget('home_stats');
    }
}
