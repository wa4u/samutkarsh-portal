<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\User;
use App\Services\ImageProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * ONE-TIME: import the WhatsApp photo archive into moderated Gallery albums.
 *
 *   php artisan gallery:import-whatsapp --base-url=https://example.com/path/
 *
 * Reads database/seeders/data/gallery_manifest.json (months → image filenames),
 * downloads each image from {base-url}{filename}, pushes it through
 * ImageProcessor (WebP + watermark), and creates one album per month.
 *
 * Albums arrive UNPUBLISHED + pending approval, so nothing is public until a
 * Trust Admin approves it. Idempotent: an album that already has items is left
 * untouched on re-run.
 */
class ImportWhatsappGallery extends Command
{
    protected $signature = 'gallery:import-whatsapp
        {--base-url= : URL prefix the image filenames are appended to}
        {--manifest=gallery_manifest.json : manifest file under database/seeders/data}
        {--limit=0 : only process the first N albums (0 = all)}';

    protected $description = 'Import the WhatsApp photo archive into moderated gallery albums (one-time)';

    public function handle(ImageProcessor $processor): int
    {
        // GD decodes full bitmaps; large mobile photos blow the default 128M.
        @ini_set('memory_limit', '512M');

        $base = rtrim((string) $this->option('base-url'), '/') . '/';
        if ($base === '/') {
            $this->error('Provide --base-url=… (where the image files are hosted).');
            return self::FAILURE;
        }

        $file = database_path('seeders/data/' . $this->option('manifest'));
        if (! is_file($file)) {
            $this->error("Manifest not found: {$file}");
            return self::FAILURE;
        }

        $userId = User::query()->orderBy('id')->value('id');
        if (! $userId) {
            $this->error('No users exist to own the albums. Create an admin first.');
            return self::FAILURE;
        }

        $albums = json_decode(file_get_contents($file), true) ?: [];
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $albums = array_slice($albums, 0, $limit);
        }

        $newAlbums = $imgOk = $imgFail = 0;

        foreach ($albums as $a) {
            $gallery = Gallery::firstOrNew(['slug' => $a['slug']]);
            if ($gallery->exists && $gallery->items()->exists()) {
                $this->line("• {$a['slug']} — already populated, skipping");
                continue;
            }
            if (! $gallery->exists) {
                $gallery->fill([
                    'user_id'         => $userId,
                    'title'           => $a['title'],
                    'description'     => 'Imported from the Samutkarsh WhatsApp group.',
                    'year'            => $a['year'] ?? null,
                    'approval_status' => 'pending',
                    'is_published'    => false,
                ])->save();
                $newAlbums++;
            }

            $sort = 0;
            foreach ($a['images'] as $filename) {
                try {
                    $resp = Http::withoutVerifying()->timeout(60)->get($base . $filename);
                    if (! $resp->ok() || ! str_starts_with((string) $resp->header('Content-Type'), 'image/')) {
                        throw new \RuntimeException('HTTP ' . $resp->status());
                    }

                    $tmp = tempnam(sys_get_temp_dir(), 'wa');
                    file_put_contents($tmp, $resp->body());
                    $basePath = $processor->process($tmp, 'gallery', watermark: true);
                    @unlink($tmp);

                    GalleryItem::create([
                        'gallery_id' => $gallery->id,
                        'type'       => 'image',
                        'image_path' => $basePath . '_display.webp',
                        'sort'       => $sort++,
                    ]);
                    $imgOk++;
                } catch (\Throwable $e) {
                    $imgFail++;
                    $this->warn("  ! {$filename}: {$e->getMessage()}");
                }
            }
            $this->info("✓ {$a['title']} — {$sort} images");
        }

        $this->newLine();
        $this->info("Done. New albums: {$newAlbums}, images imported: {$imgOk}, failed: {$imgFail}.");
        $this->info('Review & approve at /admin/galleries (all are unpublished + pending).');

        return self::SUCCESS;
    }
}
