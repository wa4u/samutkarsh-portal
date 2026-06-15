<?php

namespace App\Console\Commands;

use App\Models\Testimonial;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Import student/parent testimonials from a normalized JSON file.
 *
 *   php artisan testimonials:import-whatsapp --file=storage/app/whatsapp/new.json
 *
 * Reusable for every future batch of WhatsApp feedback (text export OR typed up
 * from screenshots): produce a JSON array of rows and run this. Each row:
 *   { "author_name": "...", "role": "Parent|Student|null",
 *     "event": "...|null", "body": "...", "date": "YYYY-MM-DD"(optional),
 *     "source_hash": "..."(optional) }
 *
 * Phone numbers are scrubbed defensively from body + name (public site). Rows
 * arrive UNPUBLISHED for review. Idempotent via source_hash (auto-derived from
 * the text when absent), so re-running never duplicates.
 */
class ImportWhatsappTestimonials extends Command
{
    protected $signature = 'testimonials:import-whatsapp
        {--file=database/seeders/data/testimonials.json : JSON file of testimonial rows}
        {--publish : import as published (default: unpublished drafts)}';

    protected $description = 'Import student/parent testimonials from a normalized JSON file';

    public function handle(): int
    {
        $path = $this->option('file');
        $full = is_file($path) ? $path : base_path($path);
        if (! is_file($full)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($full), true) ?: [];
        $created = $updated = $skipped = 0;

        foreach ($rows as $i => $r) {
            $body = self::scrub((string) ($r['body'] ?? ''));
            if (trim(strip_tags($body)) === '') {
                $skipped++;
                continue;
            }

            $name = self::scrub((string) ($r['author_name'] ?? '')) ?: ($r['role'] ?? 'Samutkarsh student');
            $hash = $r['source_hash'] ?? md5(($r['date'] ?? '') . '|' . $name . '|' . strip_tags($body));

            $existing = Testimonial::where('source_hash', $hash)->first();
            Testimonial::updateOrCreate(
                ['source_hash' => $hash],
                [
                    'author_name'  => $name,
                    'role'         => $r['role'] ?? null,
                    'event'        => $r['event'] ?? null,
                    'body'         => $body,
                    'is_published' => (bool) $this->option('publish'),
                    'is_featured'  => $existing->is_featured ?? false,
                    'sort'         => $existing->sort ?? $i,
                ]
            );
            $existing ? $updated++ : $created++;
        }

        $this->info("Testimonials — created: {$created}, updated: {$updated}, skipped: {$skipped}");
        $this->info('Review at /admin/testimonials' . ($this->option('publish') ? '' : ' (unpublished drafts).'));

        return self::SUCCESS;
    }

    /** Strip Indian phone numbers (and dangling "Cell number:" labels) from text. */
    public static function scrub(string $s): string
    {
        $s = preg_replace('/\+?91[\s\-]?\d{5}[\s\-]?\d{5}/', '', $s);
        $s = preg_replace('/\b\d{5}[\s\-]?\d{5}\b/', '', $s);
        $s = preg_replace('/\b\d{10}\b/', '', $s);
        $s = preg_replace('/(?im)^\s*(cell|mobile|phone|contact|ph)\s*(no\.?|number)?\s*[:\-]?\s*$/m', '', $s);

        return trim((string) $s);
    }
}
