<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

/**
 * Turns a large (often mobile) upload into optimised WebP derivatives.
 * (intervention/image v2 — kept on v2 for compatibility with Curator.)
 *
 * For each source it writes, under <dir>/<base>_{variant}.webp:
 *   - master  : downscaled source, NO watermark (kept for re-processing)
 *   - display : public full view, watermarked when $watermark = true
 *   - thumb   : grid/preview, no watermark
 *
 * EXIF orientation is honoured then dropped on re-encode — smaller files and
 * no leaked GPS data. Returns the base path (no suffix); callers resolve
 * variants by convention.
 */
class ImageProcessor
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(['driver' => 'gd']);
    }

    public function process(UploadedFile|string $file, string $dir, bool $watermark = false): string
    {
        $disk = Storage::disk(config('media.disk'));
        $quality = (int) config('media.webp_quality', 80);
        $sizes = config('media.sizes');

        $base = trim($dir, '/') . '/' . Str::random(24);
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        foreach (['master', 'display', 'thumb'] as $variant) {
            $img = $this->manager->make($path)->orientate();
            // Fit within the box, preserve aspect ratio, never upscale.
            $img->resize($sizes[$variant], $sizes[$variant], function ($c) {
                $c->aspectRatio();
                $c->upsize();
            });

            if ($variant === 'display' && $watermark) {
                $this->applyWatermark($img);
            }

            $disk->put("{$base}_{$variant}.webp", (string) $img->encode('webp', $quality));
            $img->destroy();
        }

        return $base;
    }

    /**
     * Delete all variants. Accepts the base path OR any variant path
     * (…_display.webp / _master / _thumb) — the suffix is normalised away.
     */
    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $base = preg_replace('/_(master|display|thumb)\.webp$/', '', $path);
        $disk = Storage::disk(config('media.disk'));
        foreach (['master', 'display', 'thumb'] as $variant) {
            $disk->delete("{$base}_{$variant}.webp");
        }
    }

    protected function applyWatermark(Image $img): void
    {
        $cfg = config('media.watermark');
        if (! ($cfg['enabled'] ?? false)) {
            return;
        }

        $disk = Storage::disk(config('media.disk'));
        $logoPath = $cfg['logo_path'] ?? null;

        if ($logoPath && $disk->exists($logoPath)) {
            $targetW = max(48, (int) round($img->width() * (($cfg['width_pct'] ?? 18) / 100)));
            $logo = $this->manager->make($disk->get($logoPath))
                ->resize($targetW, null, fn ($c) => $c->aspectRatio())
                ->opacity((int) ($cfg['opacity'] ?? 35));

            $margin = $cfg['margin'] ?? 16;
            $img->insert($logo, $cfg['position'] ?? 'bottom-right', $margin, $margin);
        }
        // (text fallback omitted on v2 — logo watermark is the configured path)
    }
}
