<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Change the footer note (bottom-right) to the developer credit "WebArt4U, Hubli".
 * Only updates when the value is still the seeded default ("Admissions …") or
 * blank, so a custom note set in admin is never overwritten. Clears the cached
 * settings map so the change shows without a manual flush.
 */
return new class extends Migration
{
    private const NEW = 'WebArt4U, Hubli';

    public function up(): void
    {
        $setting = Setting::where('key', 'footer.note')->first();

        if (! $setting) {
            Setting::create(['key' => 'footer.note', 'value' => self::NEW, 'type' => 'text', 'group' => 'footer']);
        } elseif (blank($setting->value) || Str::startsWith($setting->value, 'Admissions ')) {
            $setting->update(['value' => self::NEW]);
        }

        Cache::forget('settings.map');
    }

    public function down(): void
    {
        // Non-destructive: leave the current footer note in place.
    }
};
