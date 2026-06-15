<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Loads student/parent testimonials curated from the WhatsApp archive
 * (phone numbers scrubbed). Imported UNPUBLISHED for review before going live.
 *
 *   php artisan db:seed --class=TestimonialSeeder
 *
 * Idempotent via source_hash; never clobbers admin edits / publish decisions.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/testimonials.json');
        if (! is_file($file)) {
            $this->command?->warn('testimonials.json not found — nothing to seed.');
            return;
        }

        $rows = json_decode(file_get_contents($file), true) ?: [];
        $created = 0;

        foreach ($rows as $i => $r) {
            if (empty($r['source_hash'])) {
                continue;
            }

            $t = Testimonial::firstOrNew(['source_hash' => $r['source_hash']]);
            if ($t->exists) {
                continue;
            }

            $t->fill([
                'author_name'  => $r['author_name'],
                'role'         => $r['role'] ?? null,
                'event'        => $r['event'] ?? null,
                'body'         => $r['body'],
                'is_published' => false,
                'is_featured'  => false,
                'sort'         => $i,
            ])->save();
            $created++;
        }

        $this->command?->info("Testimonials seeded — {$created} new, " . (count($rows) - $created) . ' already present.');
    }
}
