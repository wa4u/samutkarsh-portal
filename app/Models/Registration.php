<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    protected $fillable = [
        'student_id', 'center_id', 'academic_year', 'exam_marks', 'status',
        'payment_reference', 'payment_amount', 'payment_status', 'paid_at', 'remarks',
    ];

    protected $casts = [
        'academic_year'  => 'integer',
        'exam_marks'     => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'paid_at'        => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isAdmitted(): bool
    {
        return $this->status === 'admitted';
    }
}
