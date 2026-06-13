<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Contact-form submissions (leads). Read/triage only — created by the public
 * contact form, never from the panel. Visible to Trust Admin + Education
 * Council (central admissions); gated via static methods, no Spatie perm.
 */
class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Admissions';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Inquiries';

    protected static function manage(): bool
    {
        $u = auth()->user();
        return (bool) ($u?->isTrustAdmin() || $u?->isEducationCouncil());
    }

    public static function canViewAny(): bool { return self::manage(); }
    public static function canView(Model $record): bool { return self::manage(); }
    public static function canEdit(Model $record): bool { return self::manage(); }
    public static function canCreate(): bool { return false; }
    public static function canDelete(Model $record): bool { return self::manage(); }
    public static function canDeleteAny(): bool { return self::manage(); }

    public static function getNavigationBadge(): ?string
    {
        return self::manage() ? (string) (Inquiry::where('status', 'new')->count() ?: '') : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('name')->content(fn (Inquiry $r) => $r->name),
            Forms\Components\Placeholder::make('phone')->content(fn (Inquiry $r) => $r->phone),
            Forms\Components\Placeholder::make('email')->content(fn (Inquiry $r) => $r->email ?: '—'),
            Forms\Components\Placeholder::make('center')->content(fn (Inquiry $r) => $r->center?->name ?: '—'),
            Forms\Components\Placeholder::make('subject')->content(fn (Inquiry $r) => $r->subject ?: '—'),
            Forms\Components\Placeholder::make('message')->content(fn (Inquiry $r) => $r->message)->columnSpanFull(),

            // The only editable fields — triage state + who's on it.
            Forms\Components\Select::make('status')
                ->options(['new' => 'New', 'in_progress' => 'In progress', 'closed' => 'Closed'])
                ->required(),
            Forms\Components\Select::make('handled_by')
                ->relationship('handler', 'name')
                ->label('Handled by')
                ->searchable()->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('center.name')->label('Center')->placeholder('—'),
                Tables\Columns\TextColumn::make('subject')->limit(30)->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $s) => match ($s) { 'new' => 'warning', 'in_progress' => 'info', default => 'gray' }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['new' => 'New', 'in_progress' => 'In progress', 'closed' => 'Closed']),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('View / triage')])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
