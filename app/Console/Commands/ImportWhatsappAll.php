<?php

namespace App\Console\Commands;

use Database\Seeders\ActivitySeeder;
use Database\Seeders\TestimonialSeeder;
use Illuminate\Console\Command;

/**
 * One-shot: run all WhatsApp-archive imports (activities, testimonials, photos).
 *
 *   php artisan app:import-whatsapp
 *   php artisan app:import-whatsapp --skip-photos     # text content only
 *
 * Everything lands UNPUBLISHED / pending for review. Idempotent — safe to
 * re-run after a deploy.
 */
class ImportWhatsappAll extends Command
{
    protected $signature = 'app:import-whatsapp
        {--base-url=https://kamatrelocation.com/kiran/family/ : where the photo files are hosted}
        {--skip-photos : import activities + testimonials only (no image download)}';

    protected $description = 'Run all WhatsApp-archive imports (activities, testimonials, galleries)';

    public function handle(): int
    {
        $this->components->info('Importing activities…');
        $this->call('db:seed', ['--class' => ActivitySeeder::class, '--force' => true]);

        $this->components->info('Importing testimonials…');
        $this->call('db:seed', ['--class' => TestimonialSeeder::class, '--force' => true]);

        if ($this->option('skip-photos')) {
            $this->components->warn('Skipping photo galleries (--skip-photos).');
        } else {
            $this->components->info('Importing photo galleries (this can take several minutes)…');
            $this->call('gallery:import-whatsapp', ['--base-url' => $this->option('base-url')]);
        }

        $this->newLine();
        $this->components->info('Done. Review & publish in admin → Content (Activities, Testimonials) and Galleries.');

        return self::SUCCESS;
    }
}
