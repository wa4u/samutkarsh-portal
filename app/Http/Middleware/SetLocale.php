<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the application locale from the `{locale}` route prefix (hi|kn).
 * English is the default and lives at the root with no prefix, so requests
 * without the prefix never reach this middleware and stay 'en'.
 */
class SetLocale
{
    public const SUPPORTED = ['en', 'hi', 'kn'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (in_array($locale, ['hi', 'kn'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
