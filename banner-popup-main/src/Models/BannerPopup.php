<?php

namespace Inudev\BannerPopup\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BannerPopup extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
        'trigger',
        'trigger_delay',
        'target_pages',
        'link_url',
        'link_target',
        'starts_at',
        'ends_at',
        'show_frequency',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_pages' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trigger_delay' => 'integer',
        'show_frequency' => 'integer',
    ];

    public function getTable(): string
    {
        return config('banner-popup.table_name', 'banner_popups');
    }

    // -------------------------------------------------------------------------
    // Media collections
    // -------------------------------------------------------------------------

    public function registerMediaCollections(): void
    {
        $accepted = config('banner-popup.accepted_mime_types', ['image/jpeg', 'image/png', 'image/webp']);

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $this->addMediaCollection("banner_{$breakpoint}")
                ->singleFile()
                ->acceptsMimeTypes($accepted);
        }
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->nonQueued();
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the best available image URL for a given breakpoint,
     * falling back to larger breakpoints when a specific one is missing.
     */
    public function imageUrl(string $breakpoint = 'desktop'): ?string
    {
        $fallbacks = match ($breakpoint) {
            'mobile' => ['banner_mobile', 'banner_tablet', 'banner_desktop'],
            'tablet' => ['banner_tablet', 'banner_desktop'],
            default => ['banner_desktop'],
        };

        foreach ($fallbacks as $collection) {
            $url = $this->getFirstMediaUrl($collection);
            if ($url) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Returns the thumb conversion URL for a given breakpoint.
     * Useful for admin previews.
     */
    public function thumbUrl(string $breakpoint = 'desktop'): ?string
    {
        $fallbacks = match ($breakpoint) {
            'mobile' => ['banner_mobile', 'banner_tablet', 'banner_desktop'],
            'tablet' => ['banner_tablet', 'banner_desktop'],
            default => ['banner_desktop'],
        };

        foreach ($fallbacks as $collection) {
            $url = $this->getFirstMediaUrl($collection, 'thumb');
            if ($url) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Checks is_active flag AND date window at runtime.
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Returns a plain array representation suitable for the frontend JS payload.
     */
    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'trigger' => $this->trigger,
            'trigger_delay' => $this->trigger_delay,
            'link_url' => $this->link_url,
            'link_target' => $this->link_target,
            'show_frequency' => $this->show_frequency,
            'images' => [
                'desktop' => $this->imageUrl('desktop'),
                'tablet' => $this->imageUrl('tablet'),
                'mobile' => $this->imageUrl('mobile'),
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Banners that are active right now (flag + date window).
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
