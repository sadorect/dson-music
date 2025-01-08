<?php

namespace App\Services\Analytics;

use App\Models\Track;
use App\Models\PlayHistory;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getGlobalStats()
    {
        return [
            'total_plays' => PlayHistory::count(),
            'monthly_plays' => PlayHistory::whereMonth('created_at', now()->month)->count(),
            'daily_average' => PlayHistory::where('created_at', '>=', now()->subDays(30))
                ->count() / 30
        ];
    }

    public function getPlaysByDay($days = 7)
    {
        return PlayHistory::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take($days)
            ->get();
    }

    public function getTopTracks($limit = 10)
    {
        return Track::withCount('plays')
            ->orderBy('plays_count', 'desc')
            ->take($limit)
            ->get();
    }
}
