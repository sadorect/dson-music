<?php
namespace App\Filament\Resources\ArtistProfileResource\Pages;
use App\Filament\Resources\ArtistProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListArtistProfiles extends ListRecords {
    protected static string $resource = ArtistProfileResource::class;
    protected function getHeaderActions(): array {
        return [Actions\CreateAction::make()];
    }
}
