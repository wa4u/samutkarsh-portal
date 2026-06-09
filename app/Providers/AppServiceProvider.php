<?php

namespace App\Providers;

use App\Models\User;
use App\Payments\PaymentManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
    }
}
