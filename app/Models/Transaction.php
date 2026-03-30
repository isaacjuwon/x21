<?php

namespace App\Models;

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'status',
        'reference',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Confirm a pending hold transaction.
     */
    public function confirm(?string $notes = null): bool
    {
        if ($this->status !== TransactionStatus::Pending || $this->type !== TransactionType::Hold) {
            return false;
        }

        return DB::transaction(function () use ($notes) {
            $wallet = $this->wallet;

            // Debit from balance and release hold
            $wallet->decrement('balance', $this->amount);
            $wallet->decrement('held_balance', $this->amount);

            $this->update([
                'status' => TransactionStatus::Completed,
                'notes' => $notes ?? $this->notes,
            ]);

            return true;
        });
    }

    /**
     * Void a pending hold transaction.
     */
    public function void(?string $notes = null): bool
    {
        if ($this->status !== TransactionStatus::Pending || $this->type !== TransactionType::Hold) {
            return false;
        }

        return DB::transaction(function () use ($notes) {
            $wallet = $this->wallet;

            // Simply release the hold
            $wallet->decrement('held_balance', $this->amount);

            $this->update([
                'status' => TransactionStatus::Voided,
                'notes' => $notes ?? $this->notes,
            ]);

            return true;
        });
    }
}
