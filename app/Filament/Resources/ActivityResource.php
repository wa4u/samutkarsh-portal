<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\ContentEditor;
use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Weekly session / event reports (the "Activities" diary). Imported once from
 * the WhatsApp export as unpublished drafts; reviewed and published here.
 */
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Activities';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')->required()->default(now())->native(false),
            Forms\Components\TextInput::make('center')->label('Centre / location')
                ->placeholder('Belagavi North, Raichur…')->maxLength(255)
                ->datalist(\App\Support\Centres::list())
                ->helperText('Pick an existing centre, or type a new one.'),
            Forms\Components\TextInput::make('title')->required()->maxLength(255)
                ->placeholder('Session topic / event name')->columnSpanFull(),
            ContentEditor::make('body')->label('Report'),
            Forms\Components\Toggle::make('is_published')->label('Published')->default(false)
                ->helperText('Drafts stay hidden from the website until you publish them.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('center')->placeholder('—')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('title')->limit(70)->wrap()->searchable(),
                Tables\Columns\ToggleColumn::make('is_published')->label('Published'),
                Tables\Columns\ToggleColumn::make('is_highlight')->label('Highlight'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Published'),
                Tables\Filters\TernaryFilter::make('is_highlight')->label('Highlight'),
                Tables\Filters\SelectFilter::make('center')->options(
                    fn () => Activity::query()->whereNotNull('center')->distinct()->orderBy('center')->pluck('center', 'center')->all()
                ),
            ])
            ->actions([
                Tables\Actions\Action::make('share')
                    ->icon('heroicon-o-share')->color('success')
                    ->modalHeading('Share this activity')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (Activity $record) => view('filament.activity-share', ['activity' => $record])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
