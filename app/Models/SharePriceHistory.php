<?php

namespace App\Models;

use Database\Factories\SharePriceHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharePriceHistory extends Model
{
    /** @use HasFactory<SharePriceHistoryFactory> */
    use HasFactory;

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'old_price',
        'new_price',
        'created_at',
    ];
}
