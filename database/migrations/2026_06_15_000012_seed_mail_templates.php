<?php

use App\Models\Setting;
use App\Support\MailTemplate;
use Illuminate\Database\Migrations\Migration;

/** Seed the editable registration email templates (group "mail"). */
return new class extends Migration
{
    public function up(): void
    {
        $types = [
            'mail.student_subject' => 'text',
            'mail.student_body'    => 'html',
            'mail.admin_subject'   => 'text',
            'mail.admin_body'      => 'html',
        ];

        foreach (MailTemplate::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $types[$key] ?? 'text', 'group' => 'mail'],
            );
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', array_keys(MailTemplate::DEFAULTS))->delete();
    }
};
