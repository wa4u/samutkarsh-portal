<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Activity extends Model
{
    protected $fillable = [
        'date', 'center', 'title', 'body', 'source', 'source_hash', 'is_published',
    ];

    protected $casts = [
        'date'         => 'date',
        'is_published' => 'boolean',
    ];

    /** Published, newest session first. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderByDesc('date')->orderByDesc('id');
    }

    /** Short plain-text preview for cards / lists. */
    public function excerpt(int $words = 40): string
    {
        return Str::words(trim(strip_tags($this->body)), $words, '…');
    }
}
