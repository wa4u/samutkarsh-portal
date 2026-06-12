<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Turns a large (often mobile) upload into optimised WebP derivatives.
 *
 * For each source it writes, under <dir>/<base>_{variant}.webp:
 *   - master  : downscaled source, NO watermark (kept for re-processing)
 *   - display : public full view, watermarked when $watermark = true
 *   - thumb   : grid/preview, no watermark
 *
 * EXIF is dropped (WebP re-encode) after honouring orientation — smaller files
 * and no leaked GPS data from phone cameras. Returns the base path (no suffix,
 * no extension); callers store that and resolve variants by convention.
 */
class ImageProcessor
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function process(UploadedFile|string $file, string $dir, bool $watermark = false): string
    {
        $disk = Storage::disk(config('media.disk'));
        $quality = (int) config('media.webp_quality', 80);
        $sizes = config('media.sizes');

        $base = trim($dir, '/') . '/' . Str::random(24);
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        foreach (['master', 'display', 'thumb'] as $variant) {
            $img = $this->manager->read($path);
            // Honour camera orientation, then strip metadata on encode.
            $img->scaleDown(width: $sizes[$variant], height: $sizes[$variant]);

            if ($variant === 'display' && $watermark) {
                $this->applyWatermark($img);
            }

            $disk->put("{$base}_{$variant}.webp", $img->toWebp($quality)->toString());
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

    protected function applyWatermark(ImageInterface $img): void
    {
        $cfg = config('media.watermark');
        if (! ($cfg['enabled'] ?? false)) {
            return;
        }

        $disk = Storage::disk(config('media.disk'));
        $logoPath = $cfg['logo_path'] ?? null;

        if ($logoPath && $disk->exists($logoPath)) {
            $logo = $this->manager->read($disk->get($logoPath));
            // Scale the logo to a share of the image width.
            $targetW = max(48, (int) round($img->width() * (($cfg['width_pct'] ?? 18) / 100)));
            $logo->scaleDown(width: $targetW);

            $img->place(
                $logo,
                $cfg['position'] ?? 'bottom-right',
                $cfg['margin'] ?? 16,
                $cfg['margin'] ?? 16,
                (int) ($cfg['opacity'] ?? 35),
            );
            return;
        }

        // Text fallback only if a TTF font is configured (GD text needs a font file).
        if (! empty($cfg['text']) && ! empty($cfg['font'])) {
            $img->text($cfg['text'], $img->width() - ($cfg['margin'] ?? 16), $img->height() - ($cfg['margin'] ?? 16),
                function ($font) use ($cfg) {
                    $font->filename($cfg['font']);
                    $font->size(max(14, (int) round($cfg['width_pct'] ?? 18)));
                    $font->color('rgba(255,255,255,0.6)');
                    $font->align('right');
                    $font->valign('bottom');
                });
        }
    }
}
