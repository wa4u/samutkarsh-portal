<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Recipient for "new registration" notifications (Head Office). Defaults to the
 * existing contact email so it works immediately; editable in admin → Settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        $default = Setting::where('key', 'contact.email')->value('value') ?: '';

        Setting::firstOrCreate(
            ['key' => 'notify.registration_email'],
            ['value' => $default, 'type' => 'text', 'group' => 'notifications'],
        );
    }

    public function down(): void
    {
        Setting::where('key', 'notify.registration_email')->delete();
    }
};
