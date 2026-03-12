<?php

namespace App\Filament\Resources\HomepageBannerSlideResource\Pages;

use App\Filament\Resources\HomepageBannerSlideResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomepageBannerSlide extends EditRecord
{
    protected static string $resource = HomepageBannerSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
