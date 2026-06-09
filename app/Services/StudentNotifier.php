<?php

namespace App\Services;

use App\Models\Registration;
use App\Notifications\RegistrationStatusNotification;

class StudentNotifier
{
    /**
     * Notify the student of the registration's CURRENT status, if it's a
     * notifiable status and the student is reachable. Returns true if a
     * notification was actually dispatched.
     *
     * Centralising this keeps the "should we / can we send" rules in one place;
     * callers (Filament actions, edit page) just decide whether the admin opted in.
     */
    public function notifyStatus(Registration $registration): bool
    {
        if (! RegistrationStatusNotification::isNotifiable($registration->status)) {
            return false;
        }

        $student = $registration->student;
        if (! $student || empty($student->email)) {
            return false;   // no reachable channel (mail-only for now)
        }

        $student->notify(new RegistrationStatusNotification($registration));

        return true;
    }
}
