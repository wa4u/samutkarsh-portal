<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ScopesToCenter;
use App\Filament\Resources\StudentResource\Pages;
use App\Models\Center;
use App\Models\Student;
use App\Services\ImageProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class StudentResource extends Resource
{
    use ScopesToCenter;

    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('center_id')
                    ->relationship('center', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    // Center Heads cannot choose a center: it is forced to theirs on save.
                    ->visible(fn () => ! auth()->user()?->isCenterHead())
                    ->default(fn () => auth()->user()?->center_id),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('dob')
                    ->label('Date of birth')
                    ->maxDate(now()),

                Forms\Components\Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other']),

                Forms\Components\TextInput::make('guardian_name')
                    ->maxLength(255),

                Forms\Components\TextInput::make('biometric_id')
                    ->label('Biometric / profile ref')
                    ->unique(ignoreRecord: true)
                    ->maxLength(64),

                Forms\Components\FileUpload::make('photo_path')
                    ->label('Photograph')
                    ->image()
                    // Optimised to WebP — but NO watermark on ID/profile photos.
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Forms\Get $get) {
                        $center = $get('center_id') ?: (auth()->user()?->center_id ?: 'misc');
                        return app(ImageProcessor::class)
                            ->process($file, "centers/{$center}/students", watermark: false) . '_display.webp';
                    })
                    ->deleteUploadedFileUsing(fn (?string $file) => app(ImageProcessor::class)->delete($file)),

                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('center.name')
                    ->label('Center')
                    ->sortable()
                    // The column is noise for a Center Head (always their own center).
                    ->toggleable(isToggledHiddenByDefault: fn () => auth()->user()?->isCenterHead())
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('guardian_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('center_id')
                    ->relationship('center', 'name')
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Download CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    // Centre Heads are locked to their own centre (no chooser).
                    ->form(fn () => auth()->user()?->isCenterHead() ? [] : [
                        Forms\Components\Select::make('center_id')
                            ->label('Centre')
                            ->placeholder('All centres')
                            ->options(Center::orderBy('name')->pluck('name', 'id')),
                    ])
                    ->action(fn (array $data) => redirect()->route(
                        'admin.students.export',
                        array_filter(['center' => $data['center_id'] ?? null]),
                    )),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
