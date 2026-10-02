<?php

namespace App\Models;

use App\Models\Concerns\HasProviderCodes;
use Database\Factories\DataPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPlan extends Model
{
    /** @use HasFactory<DataPlanFactory> */
    use HasFactory;

    use HasProviderCodes;

    protected $with = ['providerCodes'];

    protected $fillable = [
        'name',
        'brand_id',
        'type',
        'api_code',
        'vtpass_code',
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
