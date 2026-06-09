<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'center_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ---- Relationships ----

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // ---- Filament access ----

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['Trust Admin', 'Education Council', 'Center Head']);
    }

    // ---- Role convenience guards (used throughout the center-scoping logic) ----

    public function isTrustAdmin(): bool
    {
        return $this->hasRole('Trust Admin');
    }

    public function isEducationCouncil(): bool
    {
        return $this->hasRole('Education Council');
    }

    public function isCenterHead(): bool
    {
        return $this->hasRole('Center Head');
    }
}
