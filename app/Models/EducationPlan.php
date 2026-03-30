<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationPlan extends Model
{
    protected $fillable = [
        'brand_id',
        'type',
        'api_code',
        'price',
        'duration',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
