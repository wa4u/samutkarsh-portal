<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = [
        'gallery_id', 'type', 'image_path', 'youtube_id', 'caption', 'sort',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    // ---- Image variant URLs (image_path stores the _display.webp path) ----

    public function displayUrl(): ?string
    {
        if ($this->type !== 'image' || ! $this->image_path) {
            return null;
        }

        return Storage::disk(config('media.disk'))->url($this->image_path);
    }

    public function thumbUrl(): ?string
    {
        if ($this->type !== 'image' || ! $this->image_path) {
            return null;
        }

        $thumb = preg_replace('/_display\.webp$/', '_thumb.webp', $this->image_path);

        return Storage::disk(config('media.disk'))->url($thumb);
    }

    // ---- YouTube helpers ----

    public function youtubeEmbedUrl(): ?string
    {
        return $this->youtube_id ? "https://www.youtube.com/embed/{$this->youtube_id}" : null;
    }

    public function youtubeThumbUrl(): ?string
    {
        return $this->youtube_id ? "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg" : null;
    }

    /** Extract the 11-char video id from any common YouTube URL form. */
    public static function parseYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        // Already an id?
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) {
            return $url;
        }

        if (preg_match('%(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})%', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
