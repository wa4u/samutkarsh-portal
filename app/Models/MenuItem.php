<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    protected $fillable = [
        'parent_id', 'location', 'label', 'description', 'link_type', 'link_value', 'target_blank', 'sort', 'is_active',
    ];

    protected $casts = [
        'target_blank' => 'boolean',
        'is_active'    => 'boolean',
    ];

    /** Internal routes an admin may point a menu item at (name => label). */
    public static function routeOptions(): array
    {
        return [
            'public.home'            => 'Home',
            'public.activities'      => 'Activities',
            'public.gallery.index'   => 'Gallery',
            'public.blog.index'      => 'Blog',
            'public.testimonials'    => 'Testimonials',
            'public.register.create' => 'Register',
            'public.result.form'     => 'Result Gateway',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('is_active', true)->orderBy('sort');
    }

    /** Top-level active items for a location, with their active children. */
    public static function tree(string $location = 'header'): Collection
    {
        return static::query()
            ->where('location', $location)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with('children')
            ->orderBy('sort')
            ->get();
    }

    /** Resolve the item's href from its link type. */
    public function url(): string
    {
        return match ($this->link_type) {
            'route' => $this->link_value && Route::has($this->link_value) ? route($this->link_value) : '#',
            'page'  => $this->link_value ? url('/' . ltrim($this->link_value, '/')) : '#',
            'url'   => $this->link_value ?: '#',
            default => '#',   // 'none' — a dropdown header
        };
    }

    public function hasChildren(): bool
    {
        return $this->children->isNotEmpty();
    }
}
