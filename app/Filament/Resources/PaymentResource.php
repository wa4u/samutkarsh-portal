<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ScopesToCenter;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Read-only financial audit. Payment rows are written only by
 * AdmissionPaymentService (webhook or manual entry), so this resource has no
 * create/edit pages — see PaymentPolicy.
 */
class PaymentResource extends Resource
{
    use ScopesToCenter;

    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('registration.student.name')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('center.name')
                    ->label('Center')
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
                Tables\Columns\TextColumn::make('gateway')->badge(),
                Tables\Columns\TextColumn::make('amount')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success', 'failed' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reference')->label('Ref / UTR')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('recorder.name')->label('Recorded by')->placeholder('— webhook —')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gateway')
                    ->options(fn () => collect(config('payments.gateways'))->keys()
                        ->mapWithKeys(fn ($k) => [$k => $k])->toArray()),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['paid' => 'Paid', 'failed' => 'Failed', 'pending' => 'Pending']),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
