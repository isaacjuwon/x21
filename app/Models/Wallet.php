<?php

namespace App\Models;

use App\Enums\Wallets\WalletType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'balance',
        'held_balance',
    ];

    protected function casts(): array
    {
        return [
            'type' => WalletType::class,
            'balance' => 'decimal:2',
            'held_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get available balance (Total balance - Held balance)
     */
    public function getAvailableBalanceAttribute(): float
    {
        return (float) ($this->balance - $this->held_balance);
    }
}
