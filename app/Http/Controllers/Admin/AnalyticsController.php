<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Track;
use App\Models\PlayHistory;
use Illuminate\Http\Request;
use App\Models\ArtistProfile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    public function index()
    {
        $stats = [
            'total_plays' => PlayHistory::count(),
            'monthly_plays' => PlayHistory::whereMonth('created_at', now()->month)->count(),
            'popular_tracks' => Track::withCount('plays')
                                   ->orderBy('plays_count', 'desc')
                                   ->take(5)
                                   ->get(),
            'active_users' => User::withCount('plays')
                                 ->orderBy('plays_count', 'desc')
                                 ->take(5)
                                 ->get(),
            'plays_by_day' => PlayHistory::select(DB::raw('DATE(created_at) as date'), 
            DB::raw('count(*) as count'))
                                        ->groupBy('date')
                                        ->orderBy('date', 'desc')
                                        ->take(30)
                                        ->get()
                                        ->reverse()
                                        ->values(),
                                        'top_tracks' => Track::withCount('plays')
                                        ->orderBy('plays_count', 'desc')
                                        ->take(5)
                                        ->get()
                                            
                                      
                                        
        ];

        return view('admin.analytics.index', compact('stats'));
    }


    public function artist(ArtistProfile $artist)
    {
        $artist->total_plays = $artist->tracks()->withCount('plays')->get()->sum('plays_count');
        $artist->unique_listeners = PlayHistory::whereIn('track_id', $artist->tracks->pluck('id'))->distinct('user_id')->count();
        $artist->avg_daily_plays = PlayHistory::whereIn('track_id', $artist->tracks->pluck('id'))
            ->where('created_at', '>=', now()->subDays(30))
            ->count() / 30;
        
        $artist->top_tracks = $artist->tracks()
            ->withCount('plays')
            ->orderBy('plays_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.analytics.artist', compact('artist'));
    }

    public function export()
    {
        $data = [
            'plays_by_month' => PlayHistory::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total_plays')
            )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get(),
                
            'genre_distribution' => Track::select('genre', DB::raw('count(*) as count'))
                ->groupBy('genre')
                ->get(),
                
            'user_engagement' => User::select(
                DB::raw('DATE(created_at) as registration_date'),
                DB::raw('count(*) as new_users')
            )
                ->groupBy('registration_date')
                ->orderBy('registration_date', 'desc')
                ->take(30)
                ->get()
        ];
    
        return response()->json($data);
    }
    
    public function getArtistComparison()
    {
        $topArtists = ArtistProfile::withCount(['tracks', 'plays'])
            ->orderBy('plays_count', 'desc')
            ->take(10)
            ->get();
    
        return view('admin.analytics.artist-comparison', compact('topArtists'));
    }
    
    public function getGeographicStats()
    {
        $locationStats = PlayHistory::select('location', DB::raw('count(*) as play_count'))
            ->join('users', 'play_histories.user_id', '=', 'users.id')
            ->groupBy('location')
            ->orderBy('play_count', 'desc')
            ->get();
    
        return view('admin.analytics.geographic', compact('locationStats'));
    }
    
    public function getDailyReport()
    {
        $today = now()->format('Y-m-d');
        
        $dailyStats = [
            'new_users' => User::whereDate('created_at', $today)->count(),
            'new_tracks' => Track::whereDate('created_at', $today)->count(),
            'total_plays' => PlayHistory::whereDate('created_at', $today)->count(),
            'active_artists' => ArtistProfile::whereHas('tracks', function($query) use ($today) {
                $query->whereHas('plays', function($q) use ($today) {
                    $q->whereDate('created_at', $today);
                });
            })->count()
        ];
    
        return view('admin.analytics.daily-report', compact('dailyStats'));
    }
    

}
