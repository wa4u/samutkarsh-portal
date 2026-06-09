<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 3;

    /** @var array<string,string> */
    protected static array $typeOptions = [
        'text'    => 'Text',
        'html'    => 'HTML (sanitized on output)',
        'boolean' => 'Boolean',
        'json'    => 'JSON',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Dotted key, e.g. site.helpline or admission.deadline'),

                Forms\Components\Select::make('type')
                    ->options(self::$typeOptions)
                    ->default('text')
                    ->required()
                    ->live(),

                Forms\Components\TextInput::make('group')
                    ->default('general')
                    ->maxLength(50),

                Forms\Components\Toggle::make('value')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'boolean'),

                Forms\Components\Textarea::make('value')
                    ->rows(6)
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get) => $get('type') !== 'boolean'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('group')->badge()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('value')->limit(60)->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(fn () => Setting::query()->distinct()->pluck('group', 'group')->toArray()),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
