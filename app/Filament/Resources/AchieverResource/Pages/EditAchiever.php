<?php

namespace App\Filament\Resources\AchieverResource\Pages;

use App\Filament\Resources\AchieverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAchiever extends EditRecord
{
    protected static string $resource = AchieverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
