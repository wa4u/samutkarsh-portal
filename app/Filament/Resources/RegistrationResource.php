<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ScopesToCenter;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use App\Payments\PaymentManager;
use App\Services\AdmissionPaymentService;
use App\Services\StudentNotifier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegistrationResource extends Resource
{
    use ScopesToCenter;

    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 2;

    /** @var array<string,string> */
    protected static array $statusOptions = [
        'pending'      => 'Pending',
        'selected'     => 'Selected',
        'not_selected' => 'Not Selected',
        'admitted'     => 'Admitted',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('center_id')
                    ->relationship('center', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn () => ! auth()->user()?->isCenterHead())
                    ->default(fn () => auth()->user()?->center_id),

                Forms\Components\Select::make('student_id')
                    ->relationship(
                        'student',
                        'name',
                        // Only offer students from centers the user can see.
                        fn ($query) => $query->when(
                            auth()->user()?->isCenterHead(),
                            fn ($q) => $q->where('center_id', auth()->user()->center_id),
                        ),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('academic_year')
                    ->numeric()
                    ->required()
                    ->default((int) date('Y'))
                    ->minValue(2000)
                    ->maxValue(2100),

                Forms\Components\Select::make('status')
                    ->options(self::$statusOptions)
                    ->default('pending')
                    ->required()
                    // 'admitted' is set only by the verified payment webhook.
                    ->disableOptionWhen(fn (string $value) => $value === 'admitted'),

                Forms\Components\TextInput::make('payment_reference')
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false),

                // Admin opt-in: notifications are NEVER automatic. Off by default.
                Forms\Components\Toggle::make('notify_student')
                    ->label('Email the student about this status change')
                    ->helperText('Sends only for Selected / Admitted / Not Selected, and only if the student has an email on file.')
                    ->default(false)
                    ->dehydrated(false),

                Forms\Components\Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('center.name')
                    ->label('Center')
                    ->sortable()
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
                Tables\Columns\TextColumn::make('academic_year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'selected'     => 'info',
                        'admitted'     => 'success',
                        'not_selected' => 'danger',
                        default        => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('Payment ref')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(self::$statusOptions),
                Tables\Filters\SelectFilter::make('center_id')
                    ->relationship('center', 'name')
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
            ])
            ->actions([
                // Manual score entry — gated by the score_registration permission.
                // Row is already center-filtered, so a Head can only score their own.
                Tables\Actions\Action::make('score')
                    ->label('Set Result')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn () => auth()->user()?->can('score_registration'))
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Admission result')
                            ->options(self::$statusOptions)
                            ->disableOptionWhen(fn (string $value) => $value === 'admitted')
                            ->required(),
                        Forms\Components\Toggle::make('notify_student')
                            ->label('Email the student about this result')
                            ->default(false),
                    ])
                    ->fillForm(fn (Registration $record) => [
                        'status' => $record->status,
                    ])
                    ->action(function (Registration $record, array $data): void {
                        $notify = (bool) ($data['notify_student'] ?? false);
                        unset($data['notify_student']);

                        // Guard the webhook-owned terminal state.
                        if ($record->status === 'admitted') {
                            unset($data['status']);
                        }
                        $record->update($data);

                        if ($notify) {
                            app(StudentNotifier::class)->notifyStatus($record->refresh());
                        }
                    }),

                // Manual payment entry: cash receipt or UPI-UTR confirmation.
                // Routes through AdmissionPaymentService -> registration becomes 'admitted'.
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Registration $record) =>
                        auth()->user()?->can('record_payment') && ! $record->isAdmitted())
                    ->form([
                        Forms\Components\Select::make('gateway')
                            ->options(fn () => collect(app(PaymentManager::class)->enabledManual())
                                ->map(fn ($g) => $g->label())->toArray())
                            ->required()
                            ->live()
                            ->helperText('Only manually-confirmed methods are listed here.'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->required()
                            ->default(fn (Registration $record) =>
                                $record->payment_amount ?? config('payments.admission_fee')),
                        Forms\Components\TextInput::make('reference')
                            ->label('UTR / Receipt no.')
                            // UPI must carry a UTR for audit; cash receipt no. is optional.
                            ->required(fn (Forms\Get $get) => $get('gateway') === 'upi_qr')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('note')->maxLength(500),
                        Forms\Components\Toggle::make('notify_student')
                            ->label('Email the student that their seat is confirmed')
                            ->default(true),
                    ])
                    ->action(function (Registration $record, array $data): void {
                        app(AdmissionPaymentService::class)->confirm(
                            registration: $record,
                            gateway: $data['gateway'],
                            amount: (float) $data['amount'],
                            reference: $data['reference'] ?? null,
                            meta: ['note' => $data['note'] ?? null, 'channel' => 'admin_manual'],
                            recordedBy: auth()->id(),
                        );

                        if ($data['notify_student'] ?? false) {
                            app(StudentNotifier::class)->notifyStatus($record->refresh());
                        }

                        Notification::make()
                            ->title('Payment recorded — seat admitted')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk result entry — same rules as the per-row Set Result action:
                    // permission-gated, and never touches webhook-owned 'admitted' rows.
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update status')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(fn () => auth()->user()?->can('score_registration'))
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Admission result')
                                ->options(self::$statusOptions)
                                ->disableOptionWhen(fn (string $value) => $value === 'admitted')
                                ->required(),
                            Forms\Components\Toggle::make('notify_student')
                                ->label('Email each student about this result')
                                ->helperText('Sends only for Selected / Admitted / Not Selected, and only if the student has an email on file.')
                                ->default(false),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $notify = (bool) ($data['notify_student'] ?? false);
                            $updated = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'admitted') {
                                    $skipped++;
                                    continue;
                                }

                                $record->update(['status' => $data['status']]);
                                $updated++;

                                if ($notify) {
                                    app(StudentNotifier::class)->notifyStatus($record->refresh());
                                }
                            }

                            Notification::make()
                                ->title("Status updated for {$updated} registration(s)")
                                ->body($skipped > 0 ? "{$skipped} admitted registration(s) were skipped — that status is set only by a verified payment." : null)
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
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
