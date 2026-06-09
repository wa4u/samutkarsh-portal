<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use App\Services\StudentNotifier;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RegistrationResource::enforceCenterId($data);
    }

    /**
     * Notify only when the admin opted in AND the status actually changed in this
     * save. notify_student is a non-persisted form field, read from form state.
     */
    protected function afterSave(): void
    {
        $optedIn = (bool) ($this->form->getState()['notify_student'] ?? false);

        if ($optedIn && $this->record->wasChanged('status')) {
            app(StudentNotifier::class)->notifyStatus($this->record->refresh());
        }
    }
}
