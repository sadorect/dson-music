<?php

namespace App\Jobs;

use App\Models\ArtistProfile;
use App\Models\PlayHistory;
use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateAnalyticsReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The report type (daily, weekly, monthly).
     */
    public string $reportType;

    /**
     * The date for the report.
     */
    public string $date;

    /**
     * Create a new job instance.
     */
    public function __construct(string $reportType = 'daily', ?string $date = null)
    {
        $this->reportType = $reportType;
        $this->date = $date ?? now()->toDateString();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $cacheKey = "analytics_report_{$this->reportType}_{$this->date}";

            // Generate analytics data
            $analytics = [
                'total_plays' => PlayHistory::whereDate('created_at', $this->date)->count(),
                'unique_listeners' => PlayHistory::whereDate('created_at', $this->date)
                    ->distinct('user_id')
                    ->count('user_id'),
                'total_tracks' => Track::where('status', 'published')->count(),
                'total_artists' => ArtistProfile::count(),
                'top_tracks' => Track::query()
                    ->select('tracks.*')
                    ->selectRaw('COUNT(play_histories.id) as plays_count')
                    ->leftJoin('play_histories', 'tracks.id', '=', 'play_histories.track_id')
                    ->whereDate('play_histories.created_at', $this->date)
                    ->groupBy('tracks.id')
                    ->orderByDesc('plays_count')
                    ->limit(10)
                    ->get(),
                'genre_distribution' => Track::query()
                    ->select('genre')
                    ->selectRaw('COUNT(*) as count')
                    ->where('status', 'published')
                    ->groupBy('genre')
                    ->get(),
            ];

            // Cache the report for 24 hours
            Cache::put($cacheKey, $analytics, now()->addDay());

            Log::info('Analytics report generated', [
                'report_type' => $this->reportType,
                'date' => $this->date,
                'total_plays' => $analytics['total_plays'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate analytics report', [
                'report_type' => $this->reportType,
                'date' => $this->date,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
