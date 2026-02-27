<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistProfileResource\Pages;
use App\Models\ArtistProfile;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class ArtistProfileResource extends Resource
{
    protected static ?string $model = ArtistProfile::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-musical-note';

    protected static \UnitEnum|string|null $navigationGroup = 'Music Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Artist Info')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('stage_name')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('slug')
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('bio')
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),
            Section::make('Social Links')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('website')->url(),
                    Forms\Components\TextInput::make('twitter')->maxLength(200),
                    Forms\Components\TextInput::make('instagram')->maxLength(200),
                    Forms\Components\TextInput::make('youtube')->maxLength(200),
                    Forms\Components\TextInput::make('spotify')->maxLength(200),
                ]),
            Section::make('Status')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_verified')->label('Verified'),
                    Forms\Components\Toggle::make('is_approved')->label('Approved'),
                    Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('total_plays')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('followers_count')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_donations')->money('USD')->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->boolean()->label('Verified'),
                Tables\Columns\IconColumn::make('is_approved')->boolean()->label('Approved'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved'),
                Tables\Filters\TernaryFilter::make('is_verified'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);  
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArtistProfiles::route('/'),
            'create' => Pages\CreateArtistProfile::route('/create'),
            'edit'   => Pages\EditArtistProfile::route('/{record}/edit'),
        ];
    }
}
