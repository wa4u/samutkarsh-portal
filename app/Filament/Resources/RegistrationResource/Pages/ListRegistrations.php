<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use App\Imports\RegistrationsImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // Bulk Excel/CSV import — Education Council (and Trust Admin via Gate::before).
            Actions\Action::make('import')
                ->label('Import Results')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('import_registration'))
                ->form([
                    FileUpload::make('file')
                        ->label('Spreadsheet (.xlsx / .csv)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->storeFiles(false)   // keep in temp; we read it inline, never persist
                        ->required()
                        ->helperText('Columns: center_code, phone, academic_year, exam_marks, status (optional)'),
                ])
                ->action(function (array $data): void {
                    $import = new RegistrationsImport();
                    $import->import($data['file']->getRealPath());

                    $failures = count($import->failures());
                    $unmatched = count($import->unmatched);

                    Notification::make()
                        ->title("Imported {$import->updated} result(s)")
                        ->body($failures || $unmatched
                            ? "{$failures} invalid row(s), {$unmatched} unmatched row(s) were skipped."
                            : 'All rows processed successfully.')
                        ->color($failures || $unmatched ? 'warning' : 'success')
                        ->send();
                }),
        ];
    }
}
