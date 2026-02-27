<?php
namespace App\Filament\Resources\ArtistProfileResource\Pages;
use App\Filament\Resources\ArtistProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditArtistProfile extends EditRecord {
    protected static string $resource = ArtistProfileResource::class;
    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}
