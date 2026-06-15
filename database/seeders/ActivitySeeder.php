<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

/**
 * Loads the session/event "Activities" archive (originally curated from the
 * WhatsApp group, classified once, phone numbers scrubbed). Rows arrive
 * UNPUBLISHED so they are reviewed in admin before going live.
 *
 *   php artisan db:seed --class=Database\\Seeders\\ActivitySeeder
 *
 * Idempotent: matched by source_hash, so re-running won't duplicate. Editing a
 * row's published flag (or text) in admin is preserved unless its source text
 * changes upstream.
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/activities.json');
        if (! is_file($file)) {
            $this->command?->warn('activities.json not found — nothing to seed.');
            return;
        }

        $rows = json_decode(file_get_contents($file), true) ?: [];
        $created = 0;

        foreach ($rows as $r) {
            if (empty($r['source_hash'])) {
                continue;
            }

            $activity = Activity::firstOrNew(['source_hash' => $r['source_hash']]);
            if ($activity->exists) {
                continue; // never clobber admin edits / publish decisions
            }

            $activity->fill([
                'date'         => $r['date'],
                'center'       => $r['center'] ?? null,
                'title'        => $r['title'],
                'body'         => $r['body'],
                'source'       => $r['source'] ?? 'whatsapp',
                'is_published' => false,
            ])->save();
            $created++;
        }

        $this->command?->info("Activities seeded — {$created} new, " . (count($rows) - $created) . ' already present.');
    }
}
