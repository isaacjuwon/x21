<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'api_code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }

    public function airtimePlans()
    {
        return $this->hasMany(AirtimePlan::class);
    }

    public function dataPlans()
    {
        return $this->hasMany(DataPlan::class);
    }

    public function cablePlans()
    {
        return $this->hasMany(CablePlan::class);
    }

    public function educationPlans()
    {
        return $this->hasMany(EducationPlan::class);
    }

    public function electricityPlans()
    {
        return $this->hasMany(ElectricityPlan::class);
    }
}
