<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoodResource\Pages;
use App\Models\Mood;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MoodResource extends Resource
{
    protected static ?string $model = Mood::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-sparkles';

    protected static \UnitEnum|string|null $navigationGroup = 'Music Catalog';

    protected static ?string $navigationLabel = 'Moods';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Mood')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TagsInput::make('related_genres')
                        ->label('Related genre slugs')
                        ->placeholder('electronic')
                        ->helperText('Used to suggest this mood automatically when an artist selects a genre.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(),
                Tables\Columns\TagsColumn::make('related_genres')
                    ->label('Genre mapping'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoods::route('/'),
            'create' => Pages\CreateMood::route('/create'),
            'edit' => Pages\EditMood::route('/{record}/edit'),
        ];
    }
}
