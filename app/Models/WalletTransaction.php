<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WalletType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'status',
        'from_balance',
        'to_balance',
        'wallet_type',
        'ip_address',
        'amount',
        'notes',
        'reference',
        'transaction_type',
    ];

    protected $casts = [
        'wallet_type' => WalletType::class,
        'from_balance' => 'decimal:2',
        'to_balance' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isIncrement(): bool
    {
        return $this->transaction_type === 'increment';
    }

    public function isDecrement(): bool
    {
        return $this->transaction_type === 'decrement';
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }
}
