<?php

namespace App\Models;

use App\Enums\PropertyListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'listing_type',
        'price',
        'price_per_sqft',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'bedrooms',
        'bathrooms',
        'area_sqft',
        'year_built',
        'has_garage',
        'is_furnished',
        'parking_spaces',
        'features',
        'images',
        'slug',
        'meta_title',
        'meta_description',
        'is_featured',
        'is_active',
        'featured_until',
        'owner_name',
        'owner_email',
        'owner_phone',
    ];

    protected function casts(): array
    {
        return [
            'type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'listing_type' => PropertyListingType::class,
            'features' => 'array',
            'images' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'has_garage' => 'boolean',
            'is_furnished' => 'boolean',
            'featured_until' => 'datetime',
            'price' => 'decimal:2',
            'price_per_sqft' => 'decimal:2',
            'latitude' => 'decimal:2',
            'longitude' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title).'-'.uniqid();
            }
        });

        static::updating(function ($property) {
            if ($property->isDirty('title')) {
                $property->slug = Str::slug($property->title).'-'.uniqid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    #[Scope]
    public function available(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', PropertyStatus::Available);
    }

    #[Scope]
    public function forSale(Builder $query): Builder
    {
        return $query->where('listing_type', PropertyListingType::Sale);
    }

    #[Scope]
    public function forRent(Builder $query): Builder
    {
        return $query->where('listing_type', PropertyListingType::Rent);
    }

    #[Scope]
    public function featured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->where(function ($q) {
            $q->whereNull('featured_until')->orWhere('featured_until', '>=', now());
        });
    }

    #[Scope]
    public function inCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'like', '%'.$city.'%');
    }

    #[Scope]
    public function priceBetween(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    #[Scope]
    public function byType(Builder $query, PropertyType|string $type): Builder
    {
        return $query->where('type', $type);
    }

    #[Scope]
    public function withBedrooms(Builder $query, int $bedrooms): Builder
    {
        return $query->where('bedrooms', '>=', $bedrooms);
    }

    public function getFormattedPriceAttribute(): string
    {
        return \Illuminate\Support\Number::currency($this->price);
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->city}, {$this->state}, {$this->country}, {$this->postal_code}";
    }

    public function getMainImageAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function getImageUrlsAttribute(): array
    {
        return array_map(function ($image) {
            return asset('storage/properties/'.$image);
        }, $this->images ?? []);
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status->getColor();
    }

    public function getTypeIconAttribute(): string
    {
        return $this->type->getIcon();
    }

    public function isFeatured(): bool
    {
        return $this->is_featured && (is_null($this->featured_until) || $this->featured_until->isFuture());
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->status === PropertyStatus::Available;
    }

    public function calculatePricePerSqft(): float
    {
        if ($this->area_sqft > 0) {
            return (float) ($this->price / $this->area_sqft);
        }

        return 0;
    }

    public function enquiries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function addFeature(string $feature): void
    {
        $features = $this->features ?? [];
        if (! in_array($feature, $features)) {
            $features[] = $feature;
            $this->features = $features;
            $this->save();
        }
    }

    public function removeFeature(string $feature): void
    {
        $features = $this->features ?? [];
        if (in_array($feature, $features)) {
            $features = array_filter($features, fn ($f) => $f !== $feature);
            $this->features = array_values($features);
            $this->save();
        }
    }

    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];

        return in_array($feature, $features);
    }
}
