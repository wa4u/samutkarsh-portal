<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * One-time WEB installer for hosts with no CLI/SSH (e.g. locked-down cPanel).
 * Mirrors `php artisan app:install`, runnable from a browser.
 *
 * SECURITY: only active while INSTALL_TOKEN is set in .env, and the URL token
 * must match it (constant-time). After a successful install, REMOVE INSTALL_TOKEN
 * from .env — the route then returns 404. It also refuses to recreate an admin
 * once one exists.
 *
 *   https://your-domain/__setup?token=<INSTALL_TOKEN>
 */
class InstallController extends Controller
{
    public function __invoke(Request $request)
    {
        $configured = (string) env('INSTALL_TOKEN', '');
        $provided = (string) $request->query('token', '');

        // Disabled unless a sufficiently strong token is configured AND matches.
        abort_if(strlen($configured) < 20, 404);
        abort_unless(hash_equals($configured, $provided), 404);

        $log = [];
        $log[] = 'Samutkarsh portal — web installer';

        try {
            DB::connection()->getPdo();
            $log[] = '[ok] database connection';
        } catch (Throwable $e) {
            return $this->respond([...$log, '[FAIL] database: ' . $e->getMessage()], 500);
        }

        Artisan::call('migrate', ['--force' => true]);
        $log[] = '[ok] migrations run';
        $log[] = trim(Artisan::output());

        (new RolePermissionSeeder())->run();
        $log[] = '[ok] roles & permissions seeded';

        // Create the Trust Admin from env, only if one doesn't already exist.
        $adminExists = User::role('Trust Admin')->exists();
        if ($adminExists) {
            $log[] = '[skip] a Trust Admin already exists';
        } else {
            $email = (string) env('INSTALL_ADMIN_EMAIL', '');
            $password = (string) env('INSTALL_ADMIN_PASSWORD', '');
            if (! $email || ! $password) {
                $log[] = '[warn] INSTALL_ADMIN_EMAIL / INSTALL_ADMIN_PASSWORD not set — no admin created';
            } else {
                $admin = User::firstOrNew(['email' => $email]);
                $admin->name = env('INSTALL_ADMIN_NAME', 'Trust Admin');
                $admin->password = Hash::make($password);
                $admin->center_id = null;
                $admin->save();
                $admin->syncRoles('Trust Admin');
                $log[] = "[ok] Trust Admin created: {$email}";
            }
        }

        $log[] = '';
        $log[] = 'DONE. Now REMOVE INSTALL_TOKEN (and INSTALL_ADMIN_*) from .env to disable this page.';

        return $this->respond($log, 200);
    }

    private function respond(array $lines, int $status)
    {
        return response(implode("\n", $lines), $status)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
