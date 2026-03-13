<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SiteBranding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-swatch';

    protected static string | \UnitEnum | null $navigationGroup = 'Site Content';

    protected static ?string $navigationLabel = 'Site Branding';

    protected string $view = 'filament.pages.site-branding';

    protected ?SiteSetting $settings = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->settings = SiteSetting::current();

        $this->form->fill($this->settingsPayload());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(SiteSetting::class)
            ->components([
                Section::make('Site Identity')
                    ->description('Update the public-facing site name and browser title. Leave either blank to fall back to GrinMuzik.')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Site name')
                            ->maxLength(120)
                            ->placeholder('GrinMuzik')
                            ->helperText('Used next to the logo in navigation, footers, and other branding areas.'),
                        Forms\Components\TextInput::make('site_title')
                            ->label('Site title')
                            ->maxLength(160)
                            ->placeholder('GrinMuzik')
                            ->helperText('Used for the browser tab title when a page does not provide its own title.'),
                    ])->columns(2),
                Section::make('Social Profiles')
                    ->description('Add social media handles or full profile URLs. Only configured profiles will appear in the footer and shared branding areas.')
                    ->schema([
                        Forms\Components\TextInput::make('x_handle')
                            ->label('X / Twitter')
                            ->maxLength(255)
                            ->placeholder('@grinmuzik or https://x.com/grinmuzik')
                            ->helperText('Enter just the handle or paste the full profile URL.'),
                        Forms\Components\TextInput::make('instagram_handle')
                            ->label('Instagram')
                            ->maxLength(255)
                            ->placeholder('@grinmuzik or https://instagram.com/grinmuzik')
                            ->helperText('Enter just the handle or paste the full profile URL.'),
                        Forms\Components\TextInput::make('facebook_handle')
                            ->label('Facebook')
                            ->maxLength(255)
                            ->placeholder('grinmuzik or https://facebook.com/grinmuzik')
                            ->helperText('Enter a page handle or full profile URL.'),
                        Forms\Components\TextInput::make('tiktok_handle')
                            ->label('TikTok')
                            ->maxLength(255)
                            ->placeholder('@grinmuzik or https://tiktok.com/@grinmuzik')
                            ->helperText('Enter just the handle or paste the full profile URL.'),
                        Forms\Components\TextInput::make('youtube_handle')
                            ->label('YouTube')
                            ->maxLength(255)
                            ->placeholder('@grinmuzik or https://youtube.com/@grinmuzik')
                            ->helperText('Enter a channel handle or full channel URL.'),
                    ])->columns(2),
                Section::make('Brand Assets')
                    ->description('Update any branding asset independently. You can change only the logo, only the favicon, both, or neither.')
                    ->schema([
                        Forms\Components\FileUpload::make('site_logo')
                            ->label('Site logo')
                            ->disk('public')
                            ->directory('site-branding')
                            ->acceptedFileTypes(['image/png', 'image/webp', 'image/svg+xml'])
                            ->maxSize(2048)
                            ->helperText('Upload PNG, WEBP, or SVG. Recommended canvas: 320 x 96px with transparent background. Max 2 MB.'),
                        Forms\Components\FileUpload::make('favicon')
                            ->label('Favicon')
                            ->disk('public')
                            ->directory('site-branding')
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/svg+xml'])
                            ->maxSize(512)
                            ->helperText('Upload a square PNG, SVG, or ICO. Recommended: 64 x 64px or 512 x 512px. Max 512 KB.'),
                    ]),
                Section::make('Discovery Visibility')
                    ->description('Control which extra discovery rails appear on public pages. Core catalog and search results stay available even if you hide these cards.')
                    ->schema([
                        Forms\Components\Toggle::make('show_home_personalized')
                            ->label('Homepage For You')
                            ->helperText('Show the personalized recommendation rail for signed-in listeners.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_home_editor_picks')
                            ->label("Homepage Editor's Picks")
                            ->helperText('Show the curated editor picks rail on the homepage.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_browse_mood_filters')
                            ->label('Browse mood filters')
                            ->helperText('Show mood and vibe chips on the browse page.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_browse_editor_picks')
                            ->label("Browse Editor's Picks")
                            ->helperText('Show the featured editorial tracks rail on browse.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_browse_personalized')
                            ->label('Browse For You')
                            ->helperText('Show the personalized picks rail on browse for signed-in listeners.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_browse_fresh_this_week')
                            ->label('Browse Fresh This Week')
                            ->helperText('Show the fresh releases editorial block on browse.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_browse_artists_to_watch')
                            ->label('Browse Artists To Watch')
                            ->helperText('Show the artist spotlight block on browse.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_browse_support_direct')
                            ->label('Browse Support Direct')
                            ->helperText('Show the donation-powered tracks block on browse.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_search_trending_tracks')
                            ->label('Search Trending Tracks')
                            ->helperText('Show the suggested trending tracks card when search is empty.')
                            ->default(true),
                        Forms\Components\Toggle::make('show_search_popular_artists')
                            ->label('Search Popular Artists')
                            ->helperText('Show the suggested popular artists card when search is empty.')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->visible(fn (): bool => SiteSetting::supportsDiscoveryVisibility()),
                Section::make('Discovery Ordering')
                    ->description('Control where the editorial rails appear relative to personalized sections.')
                    ->schema([
                        Forms\Components\Select::make('home_editor_picks_position')
                            ->label("Homepage Editor's Picks position")
                            ->options([
                                'before-personalized' => 'Before For You',
                                'after-personalized' => 'After For You',
                            ])
                            ->default('after-personalized')
                            ->helperText('Choose whether the curated rail appears before or after the signed-in For You section.'),
                        Forms\Components\Select::make('browse_editor_picks_position')
                            ->label("Browse Editor's Picks position")
                            ->options([
                                'before-personalized' => 'Before For You',
                                'after-personalized' => 'After For You',
                            ])
                            ->default('before-personalized')
                            ->helperText('Choose whether the curated browse rail appears before or after the signed-in For You section.'),
                    ])
                    ->columns(2)
                    ->visible(fn (): bool => SiteSetting::supportsDiscoveryOrdering()),
            ]);
    }

    public function save(): void
    {
        $settings = $this->getSettings();
        $data = Arr::only($this->data, [
            'site_name',
            'site_title',
            'x_handle',
            'instagram_handle',
            'facebook_handle',
            'tiktok_handle',
            'youtube_handle',
        ]);

        if (SiteSetting::supportsDiscoveryVisibility()) {
            $data = array_merge($data, Arr::only($this->data, [
                'show_home_personalized',
                'show_home_editor_picks',
                'show_browse_mood_filters',
                'show_browse_editor_picks',
                'show_browse_personalized',
                'show_browse_fresh_this_week',
                'show_browse_artists_to_watch',
                'show_browse_support_direct',
                'show_search_trending_tracks',
                'show_search_popular_artists',
            ]));
        }

        if (SiteSetting::supportsDiscoveryOrdering()) {
            $data = array_merge($data, Arr::only($this->data, [
                'home_editor_picks_position',
                'browse_editor_picks_position',
            ]));
        }

        foreach (['site_logo', 'favicon'] as $attribute) {
            $data[$attribute] = $this->normalizeBrandAssetState(
                $this->data[$attribute] ?? null,
                $settings->getAttribute($attribute),
            );
        }

        $settings->forceFill($data)->save();
        $settings->refresh();
        $this->settings = $settings;

        $this->form->fill($this->settingsPayload());

        Notification::make()
            ->title('Branding updated')
            ->success()
            ->send();
    }

    protected function getSettings(): SiteSetting
    {
        return $this->settings ??= SiteSetting::current();
    }

    protected function settingsPayload(): array
    {
        $keys = [
            'site_name',
            'site_title',
            'x_handle',
            'instagram_handle',
            'facebook_handle',
            'tiktok_handle',
            'youtube_handle',
            'site_logo',
            'favicon',
        ];

        if (SiteSetting::supportsDiscoveryVisibility()) {
            $keys = array_merge($keys, [
                'show_home_personalized',
                'show_home_editor_picks',
                'show_browse_mood_filters',
                'show_browse_editor_picks',
                'show_browse_personalized',
                'show_browse_fresh_this_week',
                'show_browse_artists_to_watch',
                'show_browse_support_direct',
                'show_search_trending_tracks',
                'show_search_popular_artists',
            ]);
        }

        if (SiteSetting::supportsDiscoveryOrdering()) {
            $keys = array_merge($keys, [
                'home_editor_picks_position',
                'browse_editor_picks_position',
            ]);
        }

        return $this->getSettings()->only($keys);
    }

    protected function normalizeBrandAssetState(mixed $state, ?string $currentPath = null): ?string
    {
        if (is_array($state)) {
            $state = Arr::first($state);
        }

        if (blank($state)) {
            if ($currentPath && Storage::disk('public')->exists($currentPath)) {
                Storage::disk('public')->delete($currentPath);
            }

            return null;
        }

        if ($state instanceof TemporaryUploadedFile) {
            $newPath = $state->storeAs(
                'site-branding',
                Str::ulid() . '.' . $state->getClientOriginalExtension(),
                'public',
            );

            if (
                $currentPath &&
                ($currentPath !== $newPath) &&
                Storage::disk('public')->exists($currentPath)
            ) {
                Storage::disk('public')->delete($currentPath);
            }

            return $newPath;
        }

        if (is_string($state)) {
            return $state;
        }

        return null;
    }
}
