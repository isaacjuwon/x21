<?php

namespace App\Models;

use App\Enums\Media\MediaCollectionType;
use App\Models\Concerns\HasMediaConvertionRegistrations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Brand extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaConvertionRegistrations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'api_code',
        'status',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    // Relationships
    public function airtimePlans(): HasMany
    {
        return $this->hasMany(
            related: AirtimePlan::class,
            foreignKey: 'brand_id',
        );
    }

    public function dataPlans(): HasMany
    {
        return $this->hasMany(
            related: DataPlan::class,
            foreignKey: 'brand_id',
        );
    }

    public function cablePlans(): HasMany
    {
        return $this->hasMany(
            related: CablePlan::class,
            foreignKey: 'brand_id',
        );
    }

    public function educationPlans(): HasMany
    {
        return $this->hasMany(
            related: EducationPlan::class,
            foreignKey: 'brand_id',
        );
    }

    public function electricityPlans(): HasMany
    {
        return $this->hasMany(
            related: ElectricityPlan::class,
            foreignKey: 'brand_id',
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', MediaCollectionType::Brand);
    }

    public function getImageUrlAttribute()
    {
        return $this->image?->getUrl();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionType::Brand->value)
            ->registerMediaConversions($this->modelMediaConvertionRegistrations());
    }
}
