<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSectionResource\Pages;
use App\Models\HomeSection;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Manage the public home page layout: drag to reorder sections, toggle each
 * on/off. Trust-Admin-only. The section set is fixed (each maps to a Blade
 * partial), so creating/deleting is disabled.
 */
class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Home page';

    protected static ?string $modelLabel = 'home section';

    protected static function trust(): bool
    {
        return (bool) auth()->user()?->isTrustAdmin();
    }

    public static function canViewAny(): bool { return self::trust(); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('label')->label('Section')->weight('bold'),
                Tables\Columns\TextColumn::make('key')->badge()->color('gray'),
                Tables\Columns\ToggleColumn::make('is_enabled')->label('Shown'),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeSections::route('/'),
        ];
    }
}
