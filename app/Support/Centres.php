<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Center;
use App\Models\Testimonial;
use Illuminate\Support\Collection;

/**
 * The known centre names used for admin dropdowns / autocomplete — the union of
 * registered Centres and the free-text centre values already in Activities and
 * Testimonials, so admins reuse consistent names (and the public filters line up).
 */
class Centres
{
    /** @return array<int,string> sorted distinct centre names */
    public static function list(): array
    {
        return collect()
            ->merge(self::pluckSafe(fn () => Center::query()->pluck('name')))
            ->merge(self::pluckSafe(fn () => Activity::query()->whereNotNull('center')->distinct()->pluck('center')))
            ->merge(self::pluckSafe(fn () => Testimonial::query()->whereNotNull('center')->distinct()->pluck('center')))
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /** Guard against the query failing before a table exists (fresh migrate). */
    private static function pluckSafe(\Closure $query): Collection
    {
        try {
            return $query();
        } catch (\Throwable) {
            return collect();
        }
    }
}
