<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\ContentEditor;
use App\Filament\Resources\AchieverResource\Pages;
use App\Models\Achiever;
use App\Services\ImageProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Manage Achievers — notable alumni shown on the public /achievers pages and the
 * home page (when featured). Photos run through the same WebP pipeline as posts.
 */
class AchieverResource extends Resource
{
    protected static ?string $model = Achiever::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                    $operation === 'create' ? $set('slug', Str::slug($state)) : null),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->helperText('Auto-filled from the name; used in the page URL.'),

            Forms\Components\TextInput::make('headline')
                ->label('Achievement headline')
                ->required()
                ->maxLength(255)
                ->helperText('e.g. “Cleared UPSC CSE 2025 — AIR 128” or “Selected as PSI”.'),

            Forms\Components\Grid::make()->schema([
                Forms\Components\TextInput::make('programme')
                    ->label('Programme attended')
                    ->maxLength(255)
                    ->helperText('Optional — e.g. IAS Coaching, Utkarsh.'),
                Forms\Components\TextInput::make('year')
                    ->maxLength(20)
                    ->helperText('Optional — e.g. 2025.'),
            ])->columns(2),

            Forms\Components\FileUpload::make('photo')
                ->image()
                // Optimised to WebP (no watermark on a person's photo).
                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) =>
                    app(ImageProcessor::class)->process($file, 'achievers', watermark: false) . '_display.webp')
                ->deleteUploadedFileUsing(fn (?string $file) => app(ImageProcessor::class)->delete($file)),

            Forms\Components\Textarea::make('excerpt')
                ->label('Short story')
                ->rows(3)
                ->maxLength(500)
                ->helperText('One or two sentences, shown on cards and the home page.')
                ->columnSpanFull(),

            ContentEditor::make('story')
                ->label('Full story (optional)')
                ->helperText('Longer write-up shown on the achiever’s own page.'),

            Forms\Components\Grid::make()->schema([
                Forms\Components\Toggle::make('is_published')->label('Published')->default(true),
                Forms\Components\Toggle::make('is_featured')->label('Feature on home page')->default(false),
                Forms\Components\TextInput::make('sort')->numeric()->default(0)->helperText('Lower shows first.'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->getStateUsing(fn (Achiever $r) => $r->photoUrl())
                    ->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('headline')->limit(40)->searchable(),
                Tables\Columns\IconColumn::make('is_featured')->label('Home')->boolean(),
                Tables\Columns\ToggleColumn::make('is_published')->label('Published'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
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
            'index' => Pages\ListAchievers::route('/'),
            'create' => Pages\CreateAchiever::route('/create'),
            'edit' => Pages\EditAchiever::route('/{record}/edit'),
        ];
    }
}
