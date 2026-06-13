<?php

namespace App\Providers;

use App\Models\MenuItem;
use App\Models\User;
use App\Payments\PaymentManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, fn ($app) => new PaymentManager($app));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Trust Admin is an unconditional super-user: short-circuit every gate /
        // policy check to `true`. Returning null (not false) for everyone else lets
        // the normal Spatie permission checks run. This is the single source of
        // truth for super-admin access — no per-policy "isTrustAdmin" branches needed.
        Gate::before(function (User $user, string $ability) {
            return $user->isTrustAdmin() ? true : null;
        });

        // Share the admin-managed header menu with the public layout. Guarded so a
        // request before migrations (fresh deploy) doesn't fatal.
        View::composer('layouts.public', function ($view) {
            $menu = new Collection();
            try {
                if (Schema::hasTable('menu_items')) {
                    $menu = MenuItem::tree('header');
                }
            } catch (Throwable) {
                // table missing / DB not ready — fall back to empty (layout has defaults)
            }
            $view->with('headerMenu', $menu);
        });
    }
}
