<?php

namespace App\Support;

use App\Models\Center;
use App\Models\Setting;
use App\Models\Student;

/**
 * Admin-editable registration email templates. Subjects and bodies live in
 * Settings (group "mail") and are edited on the Email Templates admin page.
 * Tokens like {student_name} are substituted at send time.
 */
class MailTemplate
{
    /** Default subjects/bodies — also the fallback if a setting is blank. */
    public const DEFAULTS = [
        'mail.student_subject' => 'We received your registration — Samutkarsh IAS Academy {year}',
        'mail.student_body'    => '<p>Dear {student_name},</p>'
            . '<p>We have received your registration for the <strong>{year}</strong> admission cycle at <strong>{centre}</strong>.</p>'
            . '<p>Our team will review your application and get in touch. You can check your admission status anytime using the Result Gateway on our website.</p>'
            . '<p><a href="{result_url}">Check admission status</a></p>'
            . '<p>Warm regards,<br>Samutkarsh IAS Academy</p>',

        'mail.admin_subject' => 'New registration: {student_name} — {centre} ({year})',
        'mail.admin_body'    => '<p>A new applicant has registered for <strong>{year}</strong>.</p>'
            . '<ul>'
            . '<li><strong>Name:</strong> {student_name}</li>'
            . '<li><strong>Class:</strong> {class}</li>'
            . '<li><strong>School / College:</strong> {school}</li>'
            . '<li><strong>Centre:</strong> {centre}</li>'
            . '<li><strong>Phone:</strong> {phone}</li>'
            . '<li><strong>Email:</strong> {email}</li>'
            . '</ul>'
            . '<p><a href="{admin_url}">Open in admin</a></p>',

        'mail.reminder_subject' => 'Reminder from Samutkarsh IAS Academy',
        'mail.reminder_body'    => '<p>Dear {student_name},</p>'
            . '<p>This is a gentle reminder from <strong>Samutkarsh IAS Academy, {centre}</strong>.</p>'
            . '<p>Warm regards,<br>Samutkarsh IAS Academy</p>',

        'mail.birthday_subject' => 'Happy Birthday, {student_name}!',
        'mail.birthday_body'    => '<p>Dear {student_name},</p>'
            . '<p>Wishing you a very <strong>Happy Birthday</strong> from all of us at Samutkarsh IAS Academy, {centre}! '
            . 'May the year ahead bring you success, good health, and the strength to achieve your dreams.</p>'
            . '<p>Warm wishes,<br>Samutkarsh IAS Academy</p>',

        // Plain text — opened in WhatsApp via a click-to-chat (wa.me) link.
        'mail.birthday_whatsapp' => 'Dear {student_name}, wishing you a very Happy Birthday from all of us at Samutkarsh IAS Academy, {centre}! May the year ahead bring you success and good health.',
    ];

    /** Placeholders shown to admins on the Email Templates page. */
    public const PLACEHOLDERS = [
        '{student_name}', '{class}', '{school}', '{centre}', '{phone}', '{email}',
        '{year}', '{result_url}', '{admin_url}',
    ];

    /** @return array<string,string> token => value */
    public static function tokens(Student $student, Center $center, string $year): array
    {
        return [
            '{student_name}' => $student->name,
            '{class}'        => $student->classLabel() ?: '—',
            '{school}'       => $student->school_name ?: '—',
            '{centre}'       => $center->name,
            '{phone}'        => $student->phone ?: '—',
            '{email}'        => $student->email ?: '—',
            '{year}'         => $year,
            '{result_url}'   => route('public.result.form'),
            '{admin_url}'    => url('/admin/students'),
        ];
    }

    /** Plain subject line (tags stripped). */
    public static function subject(string $key, array $tokens): string
    {
        return trim(strip_tags(strtr(self::value($key), $tokens)));
    }

    /** HTML body with tokens substituted. */
    public static function body(string $key, array $tokens): string
    {
        return strtr(self::value($key), $tokens);
    }

    /**
     * Tokens for student-centric mails (reminder, birthday) where there is no
     * registration context — centre comes from the student, year is current.
     */
    public static function studentTokens(Student $student): array
    {
        return self::tokens($student, $student->center ?? new Center(['name' => 'Samutkarsh IAS Academy']), (string) now()->year);
    }

    /** Plain-text template with tokens substituted (for WhatsApp click-to-chat). */
    public static function text(string $key, array $tokens): string
    {
        return trim(strip_tags(strtr(self::value($key), $tokens)));
    }

    private static function value(string $key): string
    {
        $value = Setting::get($key);

        return filled($value) ? (string) $value : (self::DEFAULTS[$key] ?? '');
    }
}
