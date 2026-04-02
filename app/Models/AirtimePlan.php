<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirtimePlan extends Model
{
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
