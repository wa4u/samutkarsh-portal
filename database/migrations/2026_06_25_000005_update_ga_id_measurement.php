<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Google reissued the Analytics Measurement ID. Update the stored setting from
 * the old default to the new one — but only if it's still the old default or
 * blank, so a value deliberately set in admin is never clobbered. Also clears
 * the cached settings map so the change takes effect without a manual cache flush.
 */
return new class extends Migration
{
    private const OLD = 'G-TDYG1WK6KY';
    private const NEW = 'G-P1PBFZVXMZ';

    public function up(): void
    {
        $setting = Setting::where('key', 'site.ga_id')->first();

        if (! $setting) {
            Setting::create(['key' => 'site.ga_id', 'value' => self::NEW, 'type' => 'text', 'group' => 'site']);
        } elseif (blank($setting->value) || $setting->value === self::OLD) {
            $setting->update(['value' => self::NEW]);
        }

        Cache::forget('settings.map');
    }

    public function down(): void
    {
        // Non-destructive: leave the current Measurement ID in place.
    }
};
