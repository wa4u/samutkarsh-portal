<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Achiever extends Model
{
    protected $fillable = [
        'name', 'slug', 'headline', 'programme', 'year', 'photo',
        'excerpt', 'story', 'is_published', 'is_featured', 'sort',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Achiever $achiever) {
            if (blank($achiever->slug)) {
                $achiever->slug = self::uniqueSlug($achiever->name, $achiever->id);
            }
        });
    }

    /** A slug from the name, made unique with a numeric suffix if needed. */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'achiever';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }

    /** Published, featured first, then by sort, then newest. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort')
            ->latest();
    }

    /** Featured + published — the home-page set. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('sort')
            ->latest();
    }

    /** photo stores the _display.webp path (see ImageProcessor / PostResource). */
    public function photoUrl(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return Str::startsWith($this->photo, ['http://', 'https://', '/'])
            ? $this->photo
            : Storage::disk(config('media.disk'))->url($this->photo);
    }

    /** "Programme · Year" caption, omitting whichever is missing. */
    public function meta(): string
    {
        return collect([$this->programme, $this->year])->filter()->implode(' · ');
    }
}
