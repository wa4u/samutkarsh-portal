<?php

namespace App\Console\Commands;

use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ONE-TIME: import the classified WhatsApp messages as Activity drafts.
 *
 *   php artisan activities:import                 # imports type=report as unpublished
 *   php artisan activities:import --publish       # imports already published
 *
 * Reads a classification file (idx => {type, center, title}) produced after
 * parsing, joined against the parsed messages. Idempotent: re-running updates
 * the same rows (matched by a content hash) instead of duplicating.
 */
class ImportWhatsappActivities extends Command
{
    protected $signature = 'activities:import
        {--file=whatsapp/classified.json : classification JSON on the local disk}
        {--messages=whatsapp/messages.json : parsed messages JSON on the local disk}
        {--publish : import as published (default: unpublished drafts)}
        {--limit=0 : only import the first N reports (0 = all)}';

    protected $description = 'Import classified WhatsApp session reports as Activities (one-time)';

    public function handle(): int
    {
        foreach ([$this->option('file'), $this->option('messages')] as $path) {
            if (! Storage::exists($path)) {
                $this->error("Missing file: {$path}");
                return self::FAILURE;
            }
        }

        $classified = json_decode(Storage::get($this->option('file')), true) ?: [];
        $messages   = collect(json_decode(Storage::get($this->option('messages')), true) ?: [])->keyBy('idx');

        $reports = array_values(array_filter($classified, fn ($c) => ($c['type'] ?? null) === 'report'));
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $reports = array_slice($reports, 0, $limit);
        }

        $created = $updated = $skipped = 0;

        foreach ($reports as $c) {
            $msg = $messages->get($c['idx'] ?? -1);
            if (! $msg || empty($msg['date']) || empty($msg['text'])) {
                $skipped++;
                continue;
            }

            $hash = md5($msg['date'] . '|' . $msg['sender_raw'] . '|' . $msg['plain']);
            $title = Str::limit(trim($c['title'] ?? '') ?: Str::words(strip_tags($msg['plain']), 8, ''), 250, '');

            $existing = Activity::where('source_hash', $hash)->first();

            Activity::updateOrCreate(
                ['source_hash' => $hash],
                [
                    'date'         => $msg['date'],
                    'center'       => $c['center'] ?? null,
                    'title'        => $title ?: 'Session report',
                    'body'         => $msg['text'],
                    'source'       => 'whatsapp',
                    'is_published' => (bool) $this->option('publish'),
                ]
            );

            $existing ? $updated++ : $created++;
        }

        $this->info("Imported activities — created: {$created}, updated: {$updated}, skipped: {$skipped}");
        $this->info('Review them at /admin/activities (they are unpublished drafts unless --publish was used).');

        return self::SUCCESS;
    }
}
