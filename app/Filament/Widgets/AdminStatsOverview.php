<?php

namespace App\Filament\Widgets;

use App\Models\ArtistProfile;
use App\Models\Donation;
use App\Models\Track;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalUsers     = User::count();
        $totalArtists   = ArtistProfile::where('is_active', true)->count();
        $totalTracks    = Track::where('is_published', true)->count();
        $totalPlays     = Track::sum('play_count');
        $totalDonations = Donation::sum('amount');

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->icon('heroicon-o-users'),

            Stat::make('Active Artists', number_format($totalArtists))
                ->icon('heroicon-o-microphone'),

            Stat::make('Published Tracks', number_format($totalTracks))
                ->icon('heroicon-o-musical-note'),

            Stat::make('Total Plays', number_format($totalPlays))
                ->icon('heroicon-o-play'),

            Stat::make('Total Donations', '$' . number_format($totalDonations, 2))
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('New Users Today', number_format(
                User::whereDate('created_at', today())->count()
            ))
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
