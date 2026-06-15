<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    protected $fillable = [
        'author_name', 'role', 'center', 'body', 'event', 'date', 'photo', 'source_hash', 'is_published', 'is_featured', 'sort',
    ];

    protected $casts = [
        'date'         => 'date',
        'is_published' => 'boolean',
        'is_featured'  => 'boolean',
    ];

    /** Published, featured first, then newest. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort')
            ->latest();
    }

    /**
     * Render the message as safe HTML. Imported feedback already carries a
     * limited tag set (<br>, <strong>, <em>); manually-typed messages are
     * plain text, so their newlines are converted to <br>.
     */
    public function bodyHtml(): string
    {
        if (preg_match('/<(br|strong|b|em|i|p)\b/i', $this->body)) {
            return $this->body;
        }

        return nl2br(e($this->body));
    }

    public function photoUrl(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return str_starts_with($this->photo, 'http') || str_starts_with($this->photo, '/')
            ? $this->photo
            : Storage::disk('public')->url($this->photo);
    }
}
