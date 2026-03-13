<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaylistResource\Pages;
use App\Models\Playlist;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PlaylistResource extends Resource
{
    protected static ?string $model = Playlist::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-queue-list';

    protected static \UnitEnum|string|null $navigationGroup = 'Community';

    protected static ?string $navigationLabel = 'Playlists';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Playlist Details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Owner')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_public')
                        ->label('Visible on public site')
                        ->default(false),
                    Forms\Components\TextInput::make('slug')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Slug is generated from the playlist name.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(36),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tracks_count')
                    ->counts('tracks')
                    ->label('Tracks')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_public')->label('Public visibility'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Owner')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('makePublic')
                        ->label('Make public')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_public' => true])),
                    Actions\BulkAction::make('makePrivate')
                        ->label('Make private')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_public' => false])),
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaylists::route('/'),
            'edit' => Pages\EditPlaylist::route('/{record}/edit'),
        ];
    }
}
