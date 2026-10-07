<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlanProviderCode extends Model
{
    protected $fillable = [
        'planable_type',
        'planable_id',
        'provider',
        'code',
    ];

    public function planable(): MorphTo
    {
        return $this->morphTo();
    }
}
