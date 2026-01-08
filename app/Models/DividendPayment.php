<?php
// app/Models/DividendPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DividendPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dividend_id',
        'holder_type',
        'holder_id',
        'shares_held',
        'amount_per_share',
        'total_amount',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'shares_held' => 'integer',
        'amount_per_share' => 'decimal:6',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function dividend(): BelongsTo
    {
        return $this->belongsTo(Dividend::class);
    }

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }
}
