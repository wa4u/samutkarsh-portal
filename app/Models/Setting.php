<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    protected static function booted(): void
    {
        // Keep the cached lookup map fresh when settings change in admin.
        static::saved(fn () => Cache::forget('settings.map'));
        static::deleted(fn () => Cache::forget('settings.map'));
    }

    /**
     * Read a setting value by key (cached). Safe before the table exists.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $map = Cache::rememberForever('settings.map', fn () => static::pluck('value', 'key')->all());
        } catch (Throwable) {
            return $default;   // table not migrated yet
        }

        return $map[$key] ?? $default;
    }

    /**
     * Return the stored value cast according to the row's declared type.
     */
    public function castedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }
}
