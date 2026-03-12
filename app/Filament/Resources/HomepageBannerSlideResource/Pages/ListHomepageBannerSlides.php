<?php

namespace App\Filament\Resources\HomepageBannerSlideResource\Pages;

use App\Filament\Resources\HomepageBannerSlideResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomepageBannerSlides extends ListRecords
{
    protected static string $resource = HomepageBannerSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
