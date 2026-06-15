<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = ['key', 'label', 'content', 'is_enabled', 'sort'];

    protected $casts = ['is_enabled' => 'boolean', 'content' => 'array'];

    public const DEFAULT_ORDER = ['hero', 'audience', 'programmes', 'why', 'blog', 'cta'];

    /**
     * Enabled sections in order as [key => content array]. Falls back to the
     * default order (empty content → partials use their built-in text) when the
     * table hasn't been seeded yet.
     */
    public static function ordered(): array
    {
        try {
            $rows = static::query()->where('is_enabled', true)->orderBy('sort')->get(['key', 'content']);
        } catch (\Throwable) {
            // Table not migrated yet (fresh deploy) — fall back to defaults.
            return array_fill_keys(self::DEFAULT_ORDER, []);
        }

        if ($rows->isEmpty()) {
            return array_fill_keys(self::DEFAULT_ORDER, []);
        }

        return $rows->mapWithKeys(fn (self $s) => [$s->key => $s->content ?? []])->all();
    }
}
