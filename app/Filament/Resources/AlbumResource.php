<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlbumResource\Pages;
use App\Models\Album;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class AlbumResource extends Resource
{
    protected static ?string $model = Album::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'Music Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Album Details')
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
                    Forms\Components\Select::make('type')
                        ->options(['album' => 'Album', 'ep' => 'EP', 'single' => 'Single'])
                        ->required()
                        ->default('album'),
                    Forms\Components\Select::make('genre_id')
                        ->relationship('genre', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\DatePicker::make('release_date'),
                    Forms\Components\Textarea::make('description')->maxLength(1000)->columnSpanFull(),
                    Forms\Components\Toggle::make('is_published')->label('Published')->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('artist.stage_name')->label('Artist')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'album'  => 'primary',
                        'ep'     => 'success',
                        'single' => 'warning',
                        default  => 'gray',
                    }),
                Tables\Columns\TextColumn::make('genre.name'),
                Tables\Columns\TextColumn::make('play_count')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('release_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['album' => 'Album', 'ep' => 'EP', 'single' => 'Single']),
                Tables\Filters\TernaryFilter::make('is_published'),
            ])
            ->actions([Actions\EditAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);  
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlbums::route('/'),
            'create' => Pages\CreateAlbum::route('/create'),
            'edit'   => Pages\EditAlbum::route('/{record}/edit'),
        ];
    }
}
