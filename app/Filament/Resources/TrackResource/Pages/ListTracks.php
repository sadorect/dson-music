<?php

namespace App\Filament\Resources\TrackResource\Pages;

use App\Filament\Resources\TrackResource;
use App\Models\Track;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTracks extends ListRecords
{
    protected static string $resource = TrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backfillMissingMoods')
                ->label('Backfill missing moods')
                ->icon('heroicon-o-sparkles')
                ->requiresConfirmation()
                ->modalHeading('Backfill missing track moods')
                ->modalDescription('This will scan all tracks without a mood and assign one where the genre clearly maps to an existing mood.')
                ->action(function (): void {
                    $updated = 0;

                    Track::query()
                        ->with('genre')
                        ->where(function ($query) {
                            $query->whereNull('mood')->orWhere('mood', '');
                        })
                        ->chunkById(100, function ($tracks) use (&$updated): void {
                            foreach ($tracks as $track) {
                                if ($track->fillSuggestedMood()) {
                                    $track->save();
                                    $updated++;
                                }
                            }
                        });

                    Notification::make()
                        ->title($updated > 0 ? 'Missing moods backfilled' : 'No tracks needed mood backfill')
                        ->body($updated > 0
                            ? "{$updated} track(s) were updated from their genre mapping."
                            : 'Every track already has a mood or there was no clear genre-based mood to infer.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
