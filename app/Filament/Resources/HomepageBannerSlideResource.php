<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageBannerSlideResource\Pages;
use App\Models\HomepageBannerSlide;
use Closure;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HomepageBannerSlideResource extends Resource
{
    protected static ?string $model = HomepageBannerSlide::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static \UnitEnum|string|null $navigationGroup = 'Site Content';

    protected static ?string $navigationLabel = 'Homepage Banner';

    protected static function internalPageOptions(): array
    {
        return [
            route('home', absolute: false),
            route('browse', absolute: false),
            route('charts', absolute: false),
            route('new-releases', absolute: false),
            route('pricing', absolute: false),
            route('artist-guide', absolute: false),
            route('about', absolute: false),
            route('contact', absolute: false),
            route('login', absolute: false),
            route('register', absolute: false),
        ];
    }

    protected static function urlOrPathRule(): Closure
    {
        return function (string $attribute, $value, Closure $fail): void {
            if (blank($value)) {
                return;
            }

            if (str_starts_with($value, '/')) {
                return;
            }

            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return;
            }

            $fail('Use a full URL like https://example.com or an internal path like /browse.');
        };
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Slide')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                    Forms\Components\Toggle::make('show_overlay_content')
                        ->label('Show overlay content')
                        ->default(true),
                    Forms\Components\FileUpload::make('background_image')
                        ->label('Background image')
                        ->disk('public')
                        ->directory('homepage-banners')
                        ->image()
                        ->imageEditor()
                        ->imageResizeTargetWidth('1920')
                        ->imageResizeTargetHeight('960')
                        ->helperText('Recommended hero image: 1920 x 960px (2:1). Minimum 1600 x 800px. JPG or WebP works best.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('background_image_alt')
                        ->label('Background alt text')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
            Section::make('Overlay Content')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('badge_text')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('heading')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('body')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('primary_button_label')
                        ->maxLength(80),
                    Forms\Components\TextInput::make('primary_button_url')
                        ->maxLength(255)
                        ->datalist(static::internalPageOptions())
                        ->placeholder('/browse or https://example.com')
                        ->helperText('Start typing to pick an internal page, or enter any full external URL.')
                        ->rule(static::urlOrPathRule()),
                    Forms\Components\TextInput::make('secondary_button_label')
                        ->maxLength(80),
                    Forms\Components\TextInput::make('secondary_button_url')
                        ->maxLength(255)
                        ->datalist(static::internalPageOptions())
                        ->placeholder('/contact or https://example.com')
                        ->helperText('Start typing to pick an internal page, or enter any full external URL.')
                        ->rule(static::urlOrPathRule()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('background_image')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('heading')
                    ->limit(40),
                Tables\Columns\IconColumn::make('show_overlay_content')
                    ->label('Overlay')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('show_overlay_content'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('activateSelected')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Actions\BulkAction::make('deactivateSelected')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-pause-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageBannerSlides::route('/'),
            'create' => Pages\CreateHomepageBannerSlide::route('/create'),
            'edit' => Pages\EditHomepageBannerSlide::route('/{record}/edit'),
        ];
    }
}
