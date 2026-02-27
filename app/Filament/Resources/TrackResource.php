<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackResource\Pages;
use App\Models\Track;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class TrackResource extends Resource
{
    protected static ?string $model = Track::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-musical-note';

    protected static \UnitEnum|string|null $navigationGroup = 'Music Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Track Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('artist_profile_id')
                        ->relationship('artist', 'stage_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Artist'),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(200),
                    Forms\Components\Select::make('album_id')
                        ->relationship('album', 'title')
                        ->searchable()
                        ->nullable()
                        ->label('Album'),
                    Forms\Components\Select::make('genre_id')
                        ->relationship('genre', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\TextInput::make('track_number')
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                    Forms\Components\TextInput::make('duration')
                        ->numeric()
                        ->suffix('seconds')
                        ->helperText('Duration in seconds'),
                    Forms\Components\Textarea::make('description')->maxLength(1000)->columnSpanFull(),
                ]),
            Section::make('Audio File')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('audio_file_path')
                        ->label('Audio File')
                        ->disk('public')
                        ->directory('audio/tracks')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/flac', 'audio/ogg', 'audio/x-wav'])
                        ->maxSize(102400)
                        ->helperText('MP3, WAV, FLAC or OGG up to 100 MB')
                        ->columnSpanFull(),
                ]),
            Section::make('Visibility & Monetization')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_published')->label('Published')->default(false),
                    Forms\Components\Toggle::make('is_demo')->label('Demo track')->default(false),
                    Forms\Components\Toggle::make('requires_donation')->label('Requires donation to unlock'),
                    Forms\Components\TextInput::make('donation_amount')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0.5)
                        ->visible(fn (Get $get) => $get('requires_donation')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('artist.stage_name')->label('Artist')->searchable(),
                Tables\Columns\TextColumn::make('genre.name'),
                Tables\Columns\TextColumn::make('play_count')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('donation_amount')->money('USD')->sortable()->label('Unlock $'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Live'),
                Tables\Columns\IconColumn::make('requires_donation')->boolean()->label('Locked'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('requires_donation'),
                Tables\Filters\SelectFilter::make('genre_id')
                    ->relationship('genre', 'name')
                    ->label('Genre'),
            ])
            ->actions([Actions\EditAction::make()])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('publish')
                        ->label('Publish selected')
                        ->action(fn ($records) => $records->each->update(['is_published' => true]))
                        ->icon('heroicon-o-check'),
                    Actions\BulkAction::make('unpublish')
                        ->label('Unpublish selected')
                        ->action(fn ($records) => $records->each->update(['is_published' => false]))
                        ->icon('heroicon-o-x-mark'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTracks::route('/'),
            'create' => Pages\CreateTrack::route('/create'),
            'edit'   => Pages\EditTrack::route('/{record}/edit'),
        ];
    }
}
