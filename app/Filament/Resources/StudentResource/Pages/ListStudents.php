<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Imports\StudentsImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // Bulk import — anyone who can create students. Center Heads import
            // only into their own center (center_code in the file is ignored).
            Actions\Action::make('import')
                ->label('Import Students')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('create_student'))
                ->form([
                    FileUpload::make('file')
                        ->label('Spreadsheet (.xlsx / .csv)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->storeFiles(false)
                        ->required()
                        ->helperText('Columns: name, phone, email, dob, gender, guardian_name'
                            . (auth()->user()?->isCenterHead() ? '' : ', center_code')),
                ])
                ->action(function (array $data): void {
                    $forced = auth()->user()?->isCenterHead() ? auth()->user()->center_id : null;
                    $import = new StudentsImport($forced);
                    $import->import($data['file']->getRealPath());

                    $failures = count($import->failures());
                    $unmatched = count($import->unmatched);

                    Notification::make()
                        ->title("Imported {$import->imported} new, updated {$import->updated} student(s)")
                        ->body($failures || $unmatched
                            ? "{$failures} invalid row(s), {$unmatched} unmatched row(s) skipped."
                            : 'All rows processed successfully.')
                        ->color($failures || $unmatched ? 'warning' : 'success')
                        ->send();
                }),
        ];
    }
}
