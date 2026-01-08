<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WalletType;
use App\Concerns\Wallet\BalanceOperation;
use App\Concerns\Wallet\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;


final class Wallet extends Model
{
    use BalanceOperation;
    use HasFactory;
    use Loggable;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'type',
        'balance',
    ];

    protected $casts = [
        'type' => WalletType::class,
        'balance' => 'decimal:2',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }
}
