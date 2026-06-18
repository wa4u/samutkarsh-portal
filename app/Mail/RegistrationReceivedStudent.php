<?php

namespace App\Mail;

use App\Models\Center;
use App\Models\Student;
use App\Support\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Confirmation sent to the applicant after they register (editable template). */
class RegistrationReceivedStudent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $subjectLine;

    public string $bodyHtml;

    public function __construct(Student $student, Center $center, string $year)
    {
        $tokens = MailTemplate::tokens($student, $center, $year);
        $this->subjectLine = MailTemplate::subject('mail.student_subject', $tokens);
        $this->bodyHtml = MailTemplate::body('mail.student_body', $tokens);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.templated', with: [
            'subjectLine' => $this->subjectLine,
            'bodyHtml'    => $this->bodyHtml,
        ]);
    }
}
