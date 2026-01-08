<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CablePlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'api_code',
        'service_id',
        'reference',
        'type',
        'duration',
        'price',
        'discounted_price',
        'brand_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
        ];
    }

    /**
     * brand
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(
            related: Brand::class,
            foreignKey: 'brand_id'
        );
    }

    /**
     * description
     */
    protected function description(): Attribute
    {
       return Attribute::make(
           get: fn (): string => sprintf('%s %s', $this->name, $this->duration),
       );
    }

    /**
     * Image
     */
    protected function Image(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->brand->image,
        );
    }
}
