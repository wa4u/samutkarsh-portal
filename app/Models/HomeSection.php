<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = ['key', 'label', 'is_enabled', 'sort'];

    protected $casts = ['is_enabled' => 'boolean'];

    /** Ordered keys of the enabled home sections (with a safe default). */
    public static function enabledKeys(): array
    {
        $keys = static::query()->where('is_enabled', true)->orderBy('sort')->pluck('key')->all();

        return $keys ?: ['hero', 'audience', 'programmes', 'why', 'blog', 'cta'];
    }
}
