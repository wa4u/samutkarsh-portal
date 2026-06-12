<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = GalleryResource::enforceCenterId($data);
        $data['user_id'] = auth()->id();

        // Submissions from non-approvers always start as pending.
        if (! GalleryResource::canApprove()) {
            $data['approval_status'] = 'pending';
        }

        return $data;
    }
}
