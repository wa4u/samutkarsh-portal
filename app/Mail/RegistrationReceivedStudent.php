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

/** Confirmation sent to the applicant after they register. */
class RegistrationReceivedStudent extends Mailable implements ShouldQueue
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
            subject: "We received your registration — Samutkarsh IAS Academy {$this->year}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.registration-student');
    }
}
