<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = GalleryResource::enforceCenterId($data);

        // A non-approver editing an album sends it back for re-review.
        if (! GalleryResource::canApprove()) {
            $data['approval_status'] = 'pending';
        }

        return $data;
    }
}
