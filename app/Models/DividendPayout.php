<?php

namespace App\Models;

use Database\Factories\DividendPayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendPayout extends Model
{
    /** @use HasFactory<DividendPayoutFactory> */
    use HasFactory;

    protected $fillable = [
        'dividend_id',
        'user_id',
        'amount',
        'transaction_id',
    ];

    public function dividend(): BelongsTo
    {
        return $this->belongsTo(Dividend::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
