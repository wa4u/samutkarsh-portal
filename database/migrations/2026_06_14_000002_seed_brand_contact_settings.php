<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Add the new front-end settings (logo, tagline, WhatsApp, socials) to an
 * existing database so they're editable in admin right after deploy. Idempotent
 * via firstOrCreate — won't clobber values already set. Mirrors SiteSeeder for
 * fresh installs.
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'site.logo_url'    => ['', 'site'],
            'site.tagline'     => ['Nation Building through IAS', 'site'],
            'contact.whatsapp' => ['', 'contact'],
            'social.facebook'  => ['', 'social'],
            'social.instagram' => ['', 'social'],
            'social.youtube'   => ['', 'social'],
        ];

        foreach ($defaults as $key => [$value, $group]) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'type' => 'text', 'group' => $group]);
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'site.logo_url', 'site.tagline', 'contact.whatsapp',
            'social.facebook', 'social.instagram', 'social.youtube',
        ])->delete();
    }
};
