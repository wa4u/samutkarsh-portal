<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Center extends Model
{
    protected $fillable = [
        'name', 'code', 'city', 'contact_phone', 'contact_email', 'contact_timing', 'holiday_info', 'address',
        'is_active', 'is_physical', 'is_head_office', 'sort',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_physical'    => 'boolean',
        'is_head_office' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
