<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Activity extends Model
{
    protected $fillable = [
        'date', 'center', 'title', 'body', 'source', 'source_hash', 'is_published', 'is_highlight',
    ];

    protected $casts = [
        'date'         => 'date',
        'is_published' => 'boolean',
        'is_highlight' => 'boolean',
    ];

    /** Published, newest session first. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderByDesc('date')->orderByDesc('id');
    }

    /** Short plain-text preview for cards / lists / share captions. */
    public function excerpt(int $words = 40): string
    {
        return Str::words(trim(strip_tags($this->body)), $words, '…');
    }

    /** The month photo album that matches this activity (if any). */
    public function monthGallery(): ?Gallery
    {
        return Gallery::live()->where('slug', 'samutkarsh-' . $this->date->format('Y-m'))->first();
    }

    /** Absolute image URL for social previews — first photo of the month album. */
    public function shareImageUrl(): ?string
    {
        $item = $this->monthGallery()?->items()->where('type', 'image')->orderBy('sort')->first();

        return $item?->displayUrl();
    }

    /** Ready-to-post caption for social media. */
    public function shareCaption(): string
    {
        $lines = [];
        $head = collect([$this->center, optional($this->date)->format('d M Y')])->filter()->implode('  ·  ');
        if ($head) {
            $lines[] = '📍 ' . $head;
        }
        $lines[] = $this->title;
        $lines[] = '';
        $lines[] = $this->excerpt(45);
        $lines[] = '';
        $lines[] = '#SamutkarshIAS #NationBuildingThroughIAS #IAS #Karnataka';

        return implode("\n", $lines);
    }
}
