<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ScopesToCenter;
use App\Filament\Resources\GalleryResource\Pages;
use App\Filament\Resources\GalleryResource\RelationManagers\ItemsRelationManager;
use App\Models\Gallery;
use App\Services\ImageProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class GalleryResource extends Resource
{
    use ScopesToCenter;

    protected static ?string $model = Gallery::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static array $statusColors = [
        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
    ];

    public static function canApprove(): bool
    {
        return (bool) auth()->user()?->can('approve_gallery');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                Forms\Components\TextInput::make('slug')
                    ->required()->unique(ignoreRecord: true)->maxLength(255),

                Forms\Components\Select::make('center_id')
                    ->relationship('center', 'name')
                    ->label('Center (empty = Trust-wide)')
                    ->searchable()->preload()
                    ->visible(fn () => ! auth()->user()?->isCenterHead())
                    ->default(fn () => auth()->user()?->center_id),

                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->minValue(2000)->maxValue(2100)
                    ->default((int) date('Y'))
                    ->helperText('Year these photos are from — used for public filtering.'),

                Forms\Components\Textarea::make('description')->columnSpanFull(),

                Forms\Components\FileUpload::make('cover_image')
                    ->label('Cover image')
                    ->image()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Forms\Get $get) {
                        $center = $get('center_id') ?: 'trust';
                        return app(ImageProcessor::class)
                            ->process($file, "centers/{$center}/gallery", watermark: true) . '_display.webp';
                    })
                    ->deleteUploadedFileUsing(fn (?string $file) => app(ImageProcessor::class)->delete($file)),

                Forms\Components\Toggle::make('is_published')
                    ->helperText('Goes public only once an admin has also approved the album.'),

                // Moderation controls — visible only to approvers (Trust Admin).
                Forms\Components\Select::make('approval_status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->visible(fn () => static::canApprove())
                    ->dehydrated(fn () => static::canApprove()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->getStateUsing(fn (Gallery $r) => $r->coverUrl())
                    ->height(48),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('year')->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('center.name')
                    ->label('Center')->placeholder('Trust-wide')
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Items'),
                Tables\Columns\TextColumn::make('approval_status')
                    ->badge()->color(fn (string $state) => static::$statusColors[$state] ?? 'gray'),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
                Tables\Filters\SelectFilter::make('center_id')
                    ->relationship('center', 'name')
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (Gallery $r) => static::canApprove() && $r->approval_status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (Gallery $r) {
                        $r->update(['approval_status' => 'approved']);
                        Notification::make()->title('Album approved')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (Gallery $r) => static::canApprove() && $r->approval_status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(function (Gallery $r) {
                        $r->update(['approval_status' => 'rejected']);
                        Notification::make()->title('Album rejected')->warning()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
