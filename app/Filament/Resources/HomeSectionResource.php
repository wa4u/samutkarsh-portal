<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSectionResource\Pages;
use App\Models\HomeSection;
use Filament\Forms;
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
    public static function canEdit(Model $record): bool { return self::trust(); }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    /** Visible only when editing one of the given section keys. */
    protected static function forKeys(string ...$keys): \Closure
    {
        return fn (?HomeSection $record) => in_array($record?->key, $keys, true);
    }

    public static function form(Form $form): Form
    {
        // Each content.* path is declared ONCE (avoids duplicate-statePath bugs);
        // visibility decides which appear for the section being edited.
        return $form->schema([
            Forms\Components\Placeholder::make('section')
                ->content(fn (?HomeSection $record) => $record?->label)
                ->helperText('Leave a field blank to use the built-in default text.'),

            // Shared text
            Forms\Components\TextInput::make('content.heading')->label('Heading')
                ->visible(self::forKeys('why', 'programmes', 'cta', 'blog', 'testimonials', 'exam')),
            Forms\Components\Textarea::make('content.body')->label('Paragraph / text')->rows(4)
                ->visible(self::forKeys('why', 'cta')),
            Forms\Components\Textarea::make('content.intro')->label('Intro')->rows(2)
                ->visible(self::forKeys('programmes', 'testimonials', 'exam')),

            // Exam schedule banner
            Forms\Components\TextInput::make('content.reporting')->label('Reporting time')->visible(self::forKeys('exam'))
                ->placeholder('9:30 AM'),
            Forms\Components\TextInput::make('content.exam_time')->label('Exam time')->visible(self::forKeys('exam'))
                ->placeholder('10:00 AM to 12:00 PM'),
            Forms\Components\TextInput::make('content.note')->label('Note (optional)')->visible(self::forKeys('exam')),
            Forms\Components\Repeater::make('content.centres')->label('Centres & dates')->visible(self::forKeys('exam'))
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Centre')->required(),
                    Forms\Components\TextInput::make('dates')->label('Exam date(s)')->required(),
                ])->columns(2)->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['name'] ?? 'Centre'),

            // Hero
            Forms\Components\TextInput::make('content.badge')->label('Badge text')->visible(self::forKeys('hero')),
            Forms\Components\TextInput::make('content.title')->label('Heading')->visible(self::forKeys('hero')),
            Forms\Components\Textarea::make('content.subtitle')->label('Subtitle')->rows(2)->visible(self::forKeys('hero')),
            Forms\Components\FileUpload::make('content.image')->label('Background image')->image()
                ->disk('public')->directory('home')->visibility('public')->visible(self::forKeys('hero')),
            Forms\Components\TextInput::make('content.video')->label('Background video (YouTube link or MP4 URL)')->url()->visible(self::forKeys('hero'))
                ->helperText('Optional. Paste a YouTube link or an .mp4 URL — plays muted & looped. For MP4 the image above is the poster.'),
            Forms\Components\TextInput::make('content.cta_explore')->label('“Explore” button label')->visible(self::forKeys('hero')),
            Forms\Components\TextInput::make('content.cta_status')->label('“Check status” button label')->visible(self::forKeys('hero')),

            // Register button label (hero + closing CTA both have one)
            Forms\Components\TextInput::make('content.cta_primary')->label('Register button label')->visible(self::forKeys('hero', 'cta')),
            Forms\Components\TextInput::make('content.cta_secondary')->label('Secondary button label')->visible(self::forKeys('cta')),

            // Why — values + stats
            Forms\Components\TagsInput::make('content.values')->label('Value chips')->visible(self::forKeys('why'))
                ->helperText('Press Enter after each value.'),
            Forms\Components\TextInput::make('content.closing')->label('Closing line')->visible(self::forKeys('why')),
            Forms\Components\Repeater::make('content.stats')->label('Stat boxes')->visible(self::forKeys('why'))
                ->schema([
                    Forms\Components\TextInput::make('label')->required(),
                    Forms\Components\TextInput::make('value')->label('Big value')->required(),
                    Forms\Components\TextInput::make('caption'),
                ])->columns(3)->grid(2)->reorderable()->collapsible(),

            // Programmes / Audience cards
            Forms\Components\Repeater::make('content.cards')->label('Cards')->visible(self::forKeys('programmes', 'audience'))
                ->schema([
                    Forms\Components\TextInput::make('title')->required(),
                    Forms\Components\TextInput::make('desc')->label('Description'),
                    Forms\Components\TextInput::make('link')->label('Link (page slug or URL)'),
                ])->columns(3)->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['title'] ?? 'Card'),
        ]);
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
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit content'),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeSections::route('/'),
            'edit' => Pages\EditHomeSection::route('/{record}/edit'),
        ];
    }
}
