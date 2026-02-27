<?php

namespace App\Filament\Widgets;

use App\Models\Track;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTracksWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recently Uploaded Tracks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Track::with(['artistProfile', 'genre'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('artistProfile.stage_name')
                    ->label('Artist')
                    ->searchable(),

                Tables\Columns\TextColumn::make('genre.name')
                    ->label('Genre')
                    ->badge(),

                Tables\Columns\TextColumn::make('play_count')
                    ->label('Plays')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Live')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
