<?php

namespace App\Models;

use App\Enums\Banners\BannerLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'location',
        'content',
        'link_url',
        'link_text',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'location' => BannerLocation::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(800)
            ->height(300)
            ->sharpen(5)
            ->nonQueued();
    }

    /**
     * Scope: active banners for a given location, filtered by schedule window.
     */
    public function scopeActiveForLocation(Builder $query, BannerLocation|string $location): Builder
    {
        $loc = $location instanceof BannerLocation ? $location->value : $location;

        return $query
            ->where('location', $loc)
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now())
            )
            ->where(fn (Builder $q) => $q
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', now())
            )
            ->orderBy('sort_order');
    }
}
