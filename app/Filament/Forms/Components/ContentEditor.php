<?php

namespace App\Filament\Forms\Components;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Services\ImageProcessor;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * The shared rich-text editor for CMS content (posts & pages).
 *
 * TinyMCE (self-hosted, GPL) with a focused toolset: basic formatting plus
 * image / link / media. Images are click-to-resize (drag handles) and aligned
 * from the toolbar. Every inserted image is pushed through ImageProcessor, so
 * uploads are auto-downscaled and transcoded to WebP — the same optimisation
 * the gallery uses, minus the watermark (body images shouldn't be branded).
 */
class ContentEditor
{
    public static function make(string $name = 'content'): TinyEditor
    {
        return TinyEditor::make($name)
            ->profile('default')
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsVisibility('public')
            ->fileAttachmentsDirectory('editor')
            // Return the stored relative path; Filament turns it into the public
            // URL. ImageProcessor writes WebP derivatives to the 'public' disk.
            ->saveUploadedFileAttachmentsUsing(function (TemporaryUploadedFile $file): ?string {
                return app(ImageProcessor::class)
                    ->process($file, 'editor', watermark: false) . '_display.webp';
            })
            ->minHeight(400)
            ->columnSpanFull();
    }
}
