<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ScopesToCenter;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Services\ImageProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Forms\Components\ContentEditor;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostResource extends Resource
{
    use ScopesToCenter;

    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Content';

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
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                // Trust-wide when left empty; Center Heads are forced to their own center.
                Forms\Components\Select::make('center_id')
                    ->relationship('center', 'name')
                    ->label('Center (leave empty for Trust-wide)')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => ! auth()->user()?->isCenterHead())
                    ->default(fn () => auth()->user()?->center_id),

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload()
                    // Trust Admin can add a category on the fly.
                    ->createOptionForm(fn () => auth()->user()?->isTrustAdmin() ? [
                        Forms\Components\TextInput::make('name')->required(),
                    ] : null),

                Forms\Components\Textarea::make('excerpt')
                    ->maxLength(500)
                    ->columnSpanFull(),

                // TinyMCE: basic tools + image / link / media. Inline images are
                // optimised to WebP on upload and resized/aligned in the editor.
                ContentEditor::make('content')->required(),

                Forms\Components\FileUpload::make('feature_image')
                    ->image()
                    // Optimised to WebP + watermarked, like gallery images.
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Forms\Get $get) {
                        $center = $get('center_id') ?: 'trust';
                        return app(ImageProcessor::class)
                            ->process($file, "centers/{$center}/posts", watermark: true) . '_display.webp';
                    })
                    ->deleteUploadedFileUsing(fn (?string $file) => app(ImageProcessor::class)->delete($file)),

                // Optional PDFs rendered as a "Downloads" list beneath the article.
                Forms\Components\FileUpload::make('attachments')
                    ->label('Downloads (PDF)')
                    ->helperText('Optional PDF files — shown as a download list under the article.')
                    ->multiple()
                    ->reorderable()
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->disk('public')
                    ->directory('post-attachments')
                    ->visibility('public')
                    ->downloadable()
                    ->openable()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published'])
                    ->default('draft')
                    ->required()
                    ->live(),

                Forms\Components\DateTimePicker::make('published_at')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'published'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('category.name')->badge()->placeholder('—'),
                Tables\Columns\TextColumn::make('center.name')
                    ->label('Center')
                    ->placeholder('Trust-wide')
                    ->visible(fn () => ! auth()->user()?->isCenterHead()),
                Tables\Columns\TextColumn::make('author.name')->label('Author')->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published']),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
