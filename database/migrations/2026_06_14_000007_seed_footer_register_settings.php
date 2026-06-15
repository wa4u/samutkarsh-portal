<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Editable footer text + Register-CTA toggle, and upgrade the logo setting to
 * the "image" type so admin shows an uploader instead of a text box.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'footer.copyright'], [
            'value' => 'Samutkarsh IAS Academy. All rights reserved.', 'type' => 'text', 'group' => 'footer',
        ]);
        Setting::firstOrCreate(['key' => 'footer.note'], [
            'value' => 'Admissions ' . config('admissions.academic_year'), 'type' => 'text', 'group' => 'footer',
        ]);
        Setting::firstOrCreate(['key' => 'site.show_register'], [
            'value' => '1', 'type' => 'boolean', 'group' => 'site',
        ]);

        // Make the logo field an uploader in admin.
        Setting::where('key', 'site.logo_url')->update(['type' => 'image']);
    }

    public function down(): void
    {
        Setting::whereIn('key', ['footer.copyright', 'footer.note', 'site.show_register'])->delete();
        Setting::where('key', 'site.logo_url')->update(['type' => 'text']);
    }
};
