<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Gallery extends Model
{
    protected $fillable = [
        'center_id', 'user_id', 'title', 'slug', 'description',
        'cover_image', 'approval_status', 'is_published', 'sort',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Gallery $gallery) {
            if (blank($gallery->slug)) {
                $gallery->slug = Str::slug($gallery->title);
            }
        });
    }

    /** Approved AND published — the only albums the public may see. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved')->where('is_published', true);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort');
    }

    public function coverUrl(): ?string
    {
        if ($this->cover_image) {
            return Storage::disk(config('media.disk'))->url($this->cover_image);
        }

        // Fall back to the first image item's thumbnail.
        $first = $this->items->firstWhere('type', 'image');

        return $first?->thumbUrl();
    }
}
