<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a Student when an admin changes their registration status AND chooses
 * to notify. Content is defined per status; statuses without an entry below are
 * not notifiable (the admin toggle is hidden / sending is a no-op).
 *
 * ShouldQueue: with QUEUE_CONNECTION=sync (default) it sends inline; switch the
 * queue connection in production to offload to a worker — no code change needed.
 */
class RegistrationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Registration $registration) {}

    /** Statuses that have a student-facing message. */
    public static function notifiableStatuses(): array
    {
        return ['selected', 'not_selected', 'admitted'];
    }

    public static function isNotifiable(string $status): bool
    {
        return in_array($status, self::notifiableStatuses(), true);
    }

    /** Mail only when an email is on file; structured so an SMS channel can be added later. */
    public function via(object $notifiable): array
    {
        $channels = [];
        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $year = $this->registration->academic_year;
        $resultUrl = route('public.result.form');

        return match ($this->registration->status) {
            'selected' => (new MailMessage)
                ->subject("You're selected — Samutkarsh IAS Academy {$year}")
                ->greeting("Congratulations, {$notifiable->name}!")
                ->line("You have been selected in the {$year} admission process.")
                ->line('Confirm your seat by paying the admission fee through the Result Gateway.')
                ->action('Confirm your seat', $resultUrl),

            'admitted' => (new MailMessage)
                ->subject("Seat confirmed — Samutkarsh IAS Academy {$year}")
                ->greeting("Welcome aboard, {$notifiable->name}!")
                ->line("Your payment is received and your seat for {$year} is confirmed.")
                ->line('Our team will share the joining details shortly.'),

            'not_selected' => (new MailMessage)
                ->subject("Update on your application — Samutkarsh IAS Academy {$year}")
                ->greeting("Dear {$notifiable->name},")
                ->line("Thank you for applying for the {$year} cycle.")
                ->line('After careful review, we are unable to offer you a seat this time. We encourage you to apply again.'),

            default => (new MailMessage)
                ->subject('Update on your application')
                ->line('There is an update on your application status.'),
        };
    }
}
