<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ScopesToCenter;
use App\Filament\Resources\StudentResource\Pages;
use App\Mail\TemplatedStudentMail;
use App\Models\Center;
use App\Models\Student;
use App\Services\ImageProcessor;
use App\Support\MailTemplate;
use Illuminate\Support\Facades\Mail;
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

                Forms\Components\Select::make('student_class')
                    ->label('Class')
                    ->options(Student::CLASSES),

                Forms\Components\TextInput::make('school_name')
                    ->label('School / College')
                    ->maxLength(255),

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
                Tables\Columns\TextColumn::make('student_class')
                    ->label('Class')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Student::CLASSES[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('school_name')
                    ->label('School / College')
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
                Tables\Filters\SelectFilter::make('student_class')
                    ->label('Class')
                    ->options(Student::CLASSES),
                Tables\Filters\Filter::make('birthday_today')
                    ->label('Birthday today')
                    ->toggle()
                    ->query(fn ($query) => $query
                        ->whereMonth('dob', now()->month)
                        ->whereDay('dob', now()->day)),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Download CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->form(fn () => array_values(array_filter([
                        // Centre Heads are locked to their own centre (no chooser).
                        auth()->user()?->isCenterHead() ? null : Forms\Components\Select::make('center_id')
                            ->label('Centre')
                            ->placeholder('All centres')
                            ->options(Center::orderBy('name')->pluck('name', 'id')),
                        Forms\Components\Select::make('student_class')
                            ->label('Class')
                            ->placeholder('All classes')
                            ->options(Student::CLASSES),
                        Forms\Components\DatePicker::make('from')->label('Registered from')->native(false),
                        Forms\Components\DatePicker::make('to')->label('Registered to')->native(false)
                            ->helperText('Leave the dates blank to export everything.'),
                    ])))
                    ->action(fn (array $data) => redirect()->route(
                        'admin.students.export',
                        array_filter([
                            'center' => $data['center_id'] ?? null,
                            'class'  => $data['student_class'] ?? null,
                            'from'   => $data['from'] ?? null,
                            'to'     => $data['to'] ?? null,
                        ]),
                    )),
            ])
            ->actions([
                // Opens WhatsApp (wa.me) with the birthday template pre-filled;
                // the admin just hits Send in WhatsApp. No API, nothing automatic.
                Tables\Actions\Action::make('birthdayWhatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Student $record) => filled($record->phone))
                    ->url(function (Student $record): string {
                        $text = MailTemplate::text('mail.birthday_whatsapp', MailTemplate::studentTokens($record));
                        $phone = preg_replace('/\D+/', '', $record->phone);
                        if (strlen($phone) === 10) {
                            $phone = '91' . $phone;   // Indian numbers need the country code for wa.me
                        }

                        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
                    }, shouldOpenInNewTab: true),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('sendBirthdayEmail')
                        ->label('Send birthday email')
                        ->icon('heroicon-o-cake')
                        ->requiresConfirmation()
                        ->modalDescription('Sends the birthday email template to every selected student who has an email on file.')
                        ->action(function (\Illuminate\Support\Collection $records): void {
                            $sent = 0;
                            $skipped = 0;

                            foreach ($records as $student) {
                                if (empty($student->email)) {
                                    $skipped++;
                                    continue;
                                }

                                $tokens = MailTemplate::studentTokens($student);
                                Mail::to($student->email)->send(new TemplatedStudentMail(
                                    MailTemplate::subject('mail.birthday_subject', $tokens),
                                    MailTemplate::body('mail.birthday_body', $tokens),
                                ));
                                $sent++;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Birthday email sent to {$sent} student(s)")
                                ->body($skipped > 0 ? "{$skipped} student(s) skipped — no email on file." : null)
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

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
