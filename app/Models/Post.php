<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'center_id', 'category_id', 'user_id', 'title', 'slug', 'excerpt',
        'content', 'feature_image', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /** Published posts whose publish date has passed (or is unset) — the public set. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** feature_image stores the _display.webp path (see ImageProcessor). */
    public function featureImageUrl(): ?string
    {
        return $this->feature_image
            ? Storage::disk(config('media.disk'))->url($this->feature_image)
            : null;
    }
}
