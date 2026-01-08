<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirtimePlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'api_code',
        'service_id',
        'price',
        'discounted_price',
        'min_amount',
        'max_amount',
        'dial_code',
        'network_code',
        'brand_id',
    ];

    protected $append = [
      'image'  
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
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
            get: fn () => '',
        );
    }
}
