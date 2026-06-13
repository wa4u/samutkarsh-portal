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
            Forms\Components\Select::make('parent_id')
                ->label('Parent (leave empty for a top-level item)')
                ->relationship('parent', 'label', fn (Builder $q) => $q->whereNull('parent_id'))
                ->searchable()->preload(),

            Forms\Components\Select::make('link_type')
                ->options(['none' => 'No link (dropdown header)', 'route' => 'Internal page', 'page' => 'CMS page', 'url' => 'External URL'])
                ->default('none')->required()->live(),

            // One of these (all bound to link_value) shows based on link_type.
            Forms\Components\Select::make('link_value')
                ->label('Target page')
                ->options(MenuItem::routeOptions())
                ->visible(fn (Forms\Get $get) => $get('link_type') === 'route')
                ->required(fn (Forms\Get $get) => $get('link_type') === 'route'),

            Forms\Components\Select::make('link_value')
                ->label('CMS page')
                ->options(fn () => Page::orderBy('title')->pluck('title', 'slug'))
                ->searchable()
                ->visible(fn (Forms\Get $get) => $get('link_type') === 'page')
                ->required(fn (Forms\Get $get) => $get('link_type') === 'page'),

            Forms\Components\TextInput::make('link_value')
                ->label('External URL')
                ->url()
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
