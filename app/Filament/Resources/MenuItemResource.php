<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin-editable navigation. Trust-Admin-only (static can* gating, no Spatie
 * permission needed). One level of nesting: a top-level item with children
 * renders as a dropdown.
 */
class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Menu';

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
            Forms\Components\TextInput::make('label')->required()->maxLength(255),

            Forms\Components\Select::make('location')
                ->options(['header' => 'Header', 'footer' => 'Footer'])
                ->default('header')->required(),

            // Only top-level items can be parents → max one level of dropdown.
            // Plain options (not ->relationship()) to avoid Filament's relationship
            // select resolving a null model on this self-referential relation.
            Forms\Components\Select::make('parent_id')
                ->label('Parent (leave empty for a top-level item)')
                ->options(fn (?MenuItem $record) => MenuItem::query()
                    ->whereNull('parent_id')
                    ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey()))
                    ->orderBy('label')
                    ->pluck('label', 'id'))
                ->searchable(),

            Forms\Components\Select::make('link_type')
                ->options(['none' => 'No link (dropdown header)', 'route' => 'Internal page', 'page' => 'CMS page', 'url' => 'External URL'])
                ->default('none')->required()->live(),

            // Distinct fields (not persisted) that compose into link_value on save —
            // avoids the same-name collision that broke the edit screen.
            Forms\Components\Select::make('route_target')
                ->label('Target page')
                ->options(MenuItem::routeOptions())
                ->dehydrated(false)
                ->visible(fn (Forms\Get $get) => $get('link_type') === 'route')
                ->required(fn (Forms\Get $get) => $get('link_type') === 'route'),

            Forms\Components\Select::make('page_target')
                ->label('CMS page')
                ->options(fn () => Page::orderBy('title')->pluck('title', 'slug'))
                ->searchable()
                ->dehydrated(false)
                ->visible(fn (Forms\Get $get) => $get('link_type') === 'page')
                ->required(fn (Forms\Get $get) => $get('link_type') === 'page'),

            Forms\Components\TextInput::make('url_target')
                ->label('External URL')
                ->url()
                ->dehydrated(false)
                ->visible(fn (Forms\Get $get) => $get('link_type') === 'url')
                ->required(fn (Forms\Get $get) => $get('link_type') === 'url'),

            Forms\Components\Toggle::make('target_blank')
                ->label('Open in new tab')
                ->visible(fn (Forms\Get $get) => $get('link_type') === 'url'),

            Forms\Components\TextInput::make('sort')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('label')->searchable(),
                Tables\Columns\TextColumn::make('parent.label')->label('Under')->placeholder('— top level —'),
                Tables\Columns\TextColumn::make('link_type')->badge(),
                Tables\Columns\TextColumn::make('link')->getStateUsing(fn (MenuItem $r) => $r->url())->limit(40)->color('gray'),
                Tables\Columns\TextColumn::make('location')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')->options(['header' => 'Header', 'footer' => 'Footer']),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    /** Fold the per-type target field back into link_value before saving. */
    public static function composeLinkValue(array $data): array
    {
        $data['link_value'] = match ($data['link_type'] ?? 'none') {
            'route' => $data['route_target'] ?? null,
            'page'  => $data['page_target'] ?? null,
            'url'   => $data['url_target'] ?? null,
            default => null,
        };
        unset($data['route_target'], $data['page_target'], $data['url_target']);

        return $data;
    }

    /** Populate the right target field from link_value when editing. */
    public static function explodeLinkValue(array $data): array
    {
        $type = $data['link_type'] ?? null;
        $data['route_target'] = $type === 'route' ? ($data['link_value'] ?? null) : null;
        $data['page_target']  = $type === 'page' ? ($data['link_value'] ?? null) : null;
        $data['url_target']   = $type === 'url' ? ($data['link_value'] ?? null) : null;

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
