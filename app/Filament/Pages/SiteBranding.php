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

        $this->form->fill($this->settings->only([
            'site_name',
            'site_title',
            'x_handle',
            'instagram_handle',
            'facebook_handle',
            'tiktok_handle',
            'youtube_handle',
            'site_logo',
            'favicon',
        ]));
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

        foreach (['site_logo', 'favicon'] as $attribute) {
            $data[$attribute] = $this->normalizeBrandAssetState(
                $this->data[$attribute] ?? null,
                $settings->getAttribute($attribute),
            );
        }

        $settings->forceFill($data)->save();
        $settings->refresh();
        $this->settings = $settings;

        $this->form->fill($settings->only([
            'site_name',
            'site_title',
            'x_handle',
            'instagram_handle',
            'facebook_handle',
            'tiktok_handle',
            'youtube_handle',
            'site_logo',
            'favicon',
        ]));

        Notification::make()
            ->title('Branding updated')
            ->success()
            ->send();
    }

    protected function getSettings(): SiteSetting
    {
        return $this->settings ??= SiteSetting::current();
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
