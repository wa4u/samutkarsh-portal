<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Optional hero background media (image / MP4 video) settings, editable in admin.
 * Idempotent — mirrors SiteSeeder for fresh installs.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['site.hero_image', 'site.hero_video'] as $key) {
            Setting::firstOrCreate(['key' => $key], ['value' => '', 'type' => 'text', 'group' => 'site']);
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', ['site.hero_image', 'site.hero_video'])->delete();
    }
};
