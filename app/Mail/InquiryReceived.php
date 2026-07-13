<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Internal notification sent to Head Office / centre when a contact-form inquiry arrives. */
class InquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $name = trim((string) $this->inquiry->name);

        return new Envelope(subject: 'New website inquiry' . ($name !== '' ? " — {$name}" : ''));
    }

    public function content(): Content
    {
        $i = $this->inquiry;

        $rows = [
            'Name'              => $i->name,
            'Mobile'            => $i->phone,
            'Email'             => $i->email ?: '—',
            'Interested centre' => optional($i->center)->name ?: '—',
            'Subject'           => $i->subject ?: '—',
        ];

        $html = '<p><strong>A new inquiry was submitted through the website contact form.</strong></p>';
        $html .= '<table style="border-collapse:collapse; width:100%; margin:12px 0;">';
        foreach ($rows as $label => $value) {
            $html .= '<tr>'
                . '<td style="padding:6px 10px; border:1px solid #e2e8f0; background:#f8fafc; font-weight:600; white-space:nowrap;">' . e($label) . '</td>'
                . '<td style="padding:6px 10px; border:1px solid #e2e8f0;">' . e($value) . '</td>'
                . '</tr>';
        }
        $html .= '</table>';
        $html .= '<p style="margin:0 0 4px; font-weight:600;">Message</p>';
        $html .= '<div style="padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc;">'
            . nl2br(e($i->message)) . '</div>';

        return new Content(view: 'emails.templated', with: [
            'subjectLine' => 'New website inquiry',
            'bodyHtml'    => $html,
        ]);
    }
}
