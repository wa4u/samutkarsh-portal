<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Parent / student feedback (often pasted from the WhatsApp group after events).
 */
class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('author_name')->label('Name')->required()->maxLength(255),
            Forms\Components\TextInput::make('role')->label('Role / relation')->placeholder('Parent, Student…')->maxLength(255),
            Forms\Components\Textarea::make('body')->label('Message')->required()->rows(5)
                ->helperText('Paste the message as-is (any language).')->columnSpanFull(),
            Forms\Components\TextInput::make('event')->label('Event / programme (optional)')->placeholder('Village Visit 2025'),
            Forms\Components\FileUpload::make('photo')->label('Photo (optional)')->image()
                ->disk('public')->directory('testimonials')->visibility('public'),
            Forms\Components\Toggle::make('is_published')->label('Published')->default(true),
            Forms\Components\Toggle::make('is_featured')->label('Feature on home page')->default(false),
            Forms\Components\TextInput::make('sort')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('author_name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('body')->limit(60)->wrap()->label('Message'),
                Tables\Columns\TextColumn::make('event')->placeholder('—')->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')->label('Home')->boolean(),
                Tables\Columns\ToggleColumn::make('is_published')->label('Published'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
