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
                // Backfill newly-added fields only if missing — never touch the
                // body or the admin's publish / feature decisions.
                $dirty = false;
                if (blank($t->center) && filled($r['center'] ?? null)) { $t->center = $r['center']; $dirty = true; }
                if (blank($t->date) && filled($r['date'] ?? null)) { $t->date = $r['date']; $dirty = true; }
                if ($dirty) { $t->save(); }
                continue;
            }

            $t->fill([
                'author_name'  => $r['author_name'],
                'role'         => $r['role'] ?? null,
                'center'       => $r['center'] ?? null,
                'event'        => $r['event'] ?? null,
                'date'         => $r['date'] ?? null,
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
