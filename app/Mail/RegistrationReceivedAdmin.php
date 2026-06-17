<?php

namespace App\Mail;

use App\Models\Center;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sent to Head Office and the centre when a new registration comes in. */
class RegistrationReceivedAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public Center $center,
        public string $year,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New registration: {$this->student->name} — {$this->center->name} ({$this->year})",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.registration-admin');
    }
}
