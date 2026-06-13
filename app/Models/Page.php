<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'is_published', 'sort'];

    protected $casts = ['is_published' => 'boolean'];

    /** Slugs that would collide with real routes — never allow a page to use them. */
    public const RESERVED_SLUGS = [
        'admin', 'blog', 'gallery', 'register', 'result', 'checkout',
        'payments', '__setup', 'up', 'storage', 'contact',
    ];

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if (blank($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
