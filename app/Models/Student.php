<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use Notifiable;

    /** Class options: 6–9 for the school programmes (Shraddha / Medha), else College. */
    public const CLASSES = [
        '6'       => 'Class 6',
        '7'       => 'Class 7',
        '8'       => 'Class 8',
        '9'       => 'Class 9',
        'college' => 'College',
    ];

    protected $fillable = [
        'center_id', 'name', 'phone', 'email', 'dob', 'gender', 'student_class',
        'address', 'guardian_name', 'school_name', 'biometric_id', 'photo_path', 'meta',
    ];

    protected $casts = [
        'dob'  => 'date',
        'meta' => 'array',
    ];

    /** Human label for the stored class code (e.g. "Class 7", "College"). */
    public function classLabel(): ?string
    {
        return $this->student_class ? (self::CLASSES[$this->student_class] ?? $this->student_class) : null;
    }

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
