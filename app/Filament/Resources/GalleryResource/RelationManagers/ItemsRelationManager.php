<?php

namespace App\Filament\Resources\GalleryResource\RelationManagers;

use App\Models\GalleryItem;
use App\Services\ImageProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Photos & Videos';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->options(['image' => 'Image', 'youtube' => 'YouTube video'])
                ->default('image')
                ->required()
                ->live(),

            Forms\Components\FileUpload::make('image_path')
                ->label('Image')
                ->image()
                ->visible(fn (Forms\Get $get) => $get('type') === 'image')
                ->required(fn (Forms\Get $get) => $get('type') === 'image')
                ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $livewire) {
                    $center = $livewire->getOwnerRecord()->center_id ?: 'trust';
                    return app(ImageProcessor::class)
                        ->process($file, "centers/{$center}/gallery", watermark: true) . '_display.webp';
                })
                ->deleteUploadedFileUsing(fn (?string $file) => app(ImageProcessor::class)->delete($file)),

            Forms\Components\TextInput::make('youtube_id')
                ->label('YouTube URL or video ID')
                ->visible(fn (Forms\Get $get) => $get('type') === 'youtube')
                ->required(fn (Forms\Get $get) => $get('type') === 'youtube')
                // Accept any YouTube URL form; store just the 11-char id.
                ->dehydrateStateUsing(fn (?string $state) => GalleryItem::parseYoutubeId($state))
                ->rules([fn () => function (string $attribute, $value, \Closure $fail) {
                    if (filled($value) && GalleryItem::parseYoutubeId($value) === null) {
                        $fail('Enter a valid YouTube URL or 11-character video ID.');
                    }
                }]),

            Forms\Components\TextInput::make('caption')->maxLength(255)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->getStateUsing(fn (GalleryItem $r) => $r->type === 'image' ? $r->thumbUrl() : $r->youtubeThumbUrl())
                    ->height(56),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('caption')->limit(60)->wrap(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),

                // Add many photos at once — each is processed (WebP + watermark).
                Tables\Actions\Action::make('bulkUpload')
                    ->label('Bulk upload images')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray')
                    ->form([
                        Forms\Components\FileUpload::make('files')
                            ->label('Images')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->required()
                            ->storeFiles(false),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $gallery = $livewire->getOwnerRecord();
                        $center = $gallery->center_id ?: 'trust';
                        $sort = (int) $gallery->items()->max('sort');

                        foreach ($data['files'] as $file) {
                            $base = app(ImageProcessor::class)
                                ->process($file, "centers/{$center}/gallery", watermark: true);
                            $gallery->items()->create([
                                'type'       => 'image',
                                'image_path' => $base . '_display.webp',
                                'sort'       => ++$sort,
                            ]);
                        }

                        Notification::make()
                            ->title(count($data['files']) . ' image(s) added')
                            ->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
