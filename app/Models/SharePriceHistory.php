<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharePriceHistory extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'old_price',
        'new_price',
        'created_at',
    ];
}
