<?php

namespace App\Models;

use App\Models\Concerns\HasProviderCodes;
use Illuminate\Database\Eloquent\Model;

class AirtimePlan extends Model
{
    use HasProviderCodes;

    protected $with = ['providerCodes'];

    protected $fillable = [
        'name',
        'brand_id',
        'type',
        'api_code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
