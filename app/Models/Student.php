<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use Notifiable;

    protected $fillable = [
        'center_id', 'name', 'phone', 'email', 'dob', 'gender',
        'address', 'guardian_name', 'biometric_id', 'photo_path', 'meta',
    ];

    protected $casts = [
        'dob'  => 'date',
        'meta' => 'array',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /** Where mail notifications go (null = no email on file → mail channel skipped). */
    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }

    /** Phone routing for a future SMS channel (Vonage/MSG91/etc.). */
    public function routeNotificationForVonage(): ?string
    {
        return $this->phone;
    }
}
