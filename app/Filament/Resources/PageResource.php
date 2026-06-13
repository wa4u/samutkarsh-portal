<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * CMS content pages (Courses, About, etc.). Trust-Admin-only — gated via the
 * static can* methods so no Spatie permission / production re-seed is needed.
 */
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static function trust(): bool
    {
        return (bool) auth()->user()?->isTrustAdmin();
    }

    public static function canViewAny(): bool { return self::trust(); }
    public static function canCreate(): bool { return self::trust(); }
    public static function canView(Model $record): bool { return self::trust(); }
    public static function canEdit(Model $record): bool { return self::trust(); }
    public static function canDelete(Model $record): bool { return self::trust(); }
    public static function canDeleteAny(): bool { return self::trust(); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                    $operation === 'create' ? $set('slug', Str::slug($state)) : null),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->helperText('The page lives at /your-slug')
                ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                    if (in_array($value, Page::RESERVED_SLUGS, true)) {
                        $fail("'{$value}' is reserved by the system — pick another slug.");
                    }
                }),

            Forms\Components\RichEditor::make('content')->columnSpanFull(),

            Forms\Components\Toggle::make('is_published'),
            Forms\Components\TextInput::make('sort')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->color('gray')
                    ->url(fn (Page $r) => $r->is_published ? url('/' . $r->slug) : null, true),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
            ])
            ->reorderable('sort')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
