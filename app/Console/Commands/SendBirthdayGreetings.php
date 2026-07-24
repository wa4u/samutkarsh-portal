<?php

namespace App\Console\Commands;

use App\Mail\TemplatedStudentMail;
use App\Models\Setting;
use App\Models\Student;
use App\Support\MailTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBirthdayGreetings extends Command
{
    protected $signature = 'students:send-birthday-greetings {--force : Send even if the auto-send toggle is off}';

    protected $description = "Email the birthday template to students whose birthday is today (only when the admin toggle 'mail.birthday_auto' is on).";

    public function handle(): int
    {
        $enabled = filter_var(Setting::get('mail.birthday_auto', '0'), FILTER_VALIDATE_BOOLEAN);

        if (! $enabled && ! $this->option('force')) {
            $this->info('Birthday auto-send is off (enable it on the Email Templates page). Nothing sent.');

            return self::SUCCESS;
        }

        $sent = 0;
        Student::query()
            ->whereNotNull('email')->where('email', '!=', '')
            ->whereMonth('dob', now()->month)
            ->whereDay('dob', now()->day)
            ->with('center')
            ->chunkById(100, function ($students) use (&$sent) {
                foreach ($students as $student) {
                    $tokens = MailTemplate::studentTokens($student);
                    Mail::to($student->email)->send(new TemplatedStudentMail(
                        MailTemplate::subject('mail.birthday_subject', $tokens),
                        MailTemplate::body('mail.birthday_body', $tokens),
                    ));
                    $sent++;
                }
            });

        $this->info("Birthday email sent to {$sent} student(s).");

        return self::SUCCESS;
    }
}
