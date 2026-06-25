<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Google Analytics Measurement ID, editable in admin (Settings → site.ga_id).
 * Blank disables tracking. Idempotent — mirrors SiteSeeder for fresh installs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'site.ga_id'],
            ['value' => 'G-TDYG1WK6KY', 'type' => 'text', 'group' => 'site'],
        );
    }

    public function down(): void
    {
        Setting::where('key', 'site.ga_id')->delete();
    }
};
