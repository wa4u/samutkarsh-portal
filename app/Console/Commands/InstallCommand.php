<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * One-shot, idempotent production setup. Safe to re-run.
 *
 *   php artisan app:install --admin-email=you@org.in --admin-name="Admin" --admin-password=secret
 *
 * Run AFTER composer install and a configured .env (DB credentials + APP_KEY).
 */
class InstallCommand extends Command
{
    protected $signature = 'app:install
        {--admin-email= : Email for the Trust Admin account}
        {--admin-name=Trust Admin : Display name for the Trust Admin}
        {--admin-password= : Password for the Trust Admin (prompted if omitted)}';

    protected $description = 'Set up the Samutkarsh portal: migrate, seed roles, create the Trust Admin.';

    public function handle(): int
    {
        $this->info('Samutkarsh portal installer');

        // 1. App key.
        if (empty(config('app.key'))) {
            $this->warn('APP_KEY missing — generating…');
            Artisan::call('key:generate', ['--force' => true], $this->output);
        }

        // 2. Database reachable?
        try {
            DB::connection()->getPdo();
            $this->line('  ✔ database connection OK');
        } catch (Throwable $e) {
            $this->error('  ✘ cannot connect to the database. Fix your .env DB_* settings first.');
            $this->line('    ' . $e->getMessage());
            return self::FAILURE;
        }

        // 3. Schema.
        $this->line('  → running migrations…');
        Artisan::call('migrate', ['--force' => true], $this->output);

        // 4. Roles & permissions (idempotent).
        $this->line('  → seeding roles & permissions…');
        (new RolePermissionSeeder())->run();
        $this->line('  ✔ roles & permissions seeded');

        // 5. Trust Admin.
        $email = $this->option('admin-email') ?: $this->ask('Trust Admin email');
        $name = $this->option('admin-name') ?: 'Trust Admin';
        $password = $this->option('admin-password') ?: $this->secret('Trust Admin password');

        if (! $email || ! $password) {
            $this->error('Admin email and password are required.');
            return self::FAILURE;
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = $name;
        $admin->password = Hash::make($password);
        $admin->center_id = null;
        $admin->save();
        $admin->syncRoles('Trust Admin');
        $this->line("  ✔ Trust Admin ready: {$email}");

        // 6. Storage symlink (ignore if already linked).
        try {
            Artisan::call('storage:link');
            $this->line('  ✔ storage symlink ensured');
        } catch (Throwable) {
            $this->line('  • storage symlink already present');
        }

        $this->newLine();
        $this->info('Done. Log in at /admin. Remember to cache config/routes in production:');
        $this->line('  php artisan config:cache && php artisan route:cache && php artisan view:cache');

        return self::SUCCESS;
    }
}
