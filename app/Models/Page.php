<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'body',
        'content',
        'layout',
        'published_at',
        'meta_title',
        'meta_description',
        'featured_image',
    ];

    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /**
     * Scope for published pages.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->whereNull('published_at')
            ->orWhere('published_at', '>', now());
    }

    /**
     * Check if page is published.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Check if page is draft.
     */
    public function isDraft(): bool
    {
        return !$this->isPublished();
    }

    /**
     * Get the URL for this page.
     */
    public function getUrlAttribute(): string
    {
        return route('pages.show', $this->slug);
    }
}
