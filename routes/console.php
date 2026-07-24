<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly backup (DB + uploaded media). Driven by the single cron that runs
// `schedule:run` every minute. Clean old backups first, then take a fresh one.
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');

// Optional: alert if the latest backup is missing/too old/too large.
Schedule::command('backup:monitor')->daily()->at('02:00');

// Birthday emails — the command itself no-ops unless the admin has switched
// on 'mail.birthday_auto' (Email Templates page), so scheduling it is safe.
Schedule::command('students:send-birthday-greetings')->daily()->at('08:00');
