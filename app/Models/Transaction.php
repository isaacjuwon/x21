<?php

namespace App\Models;

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use App\Events\Wallets\TransactionFailed;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'status',
        'reference',
        'notes',
        'meta',
        'transactionable_id',
        'transactionable_type',
        'refund_for_id',
        'failure_reason',
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

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /** The original transaction this one is a refund for. */
    public function refundFor(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'refund_for_id');
    }

    /** The refund transaction created for this one (if any). */
    public function refund(): HasOne
    {
        return $this->hasOne(Transaction::class, 'refund_for_id');
    }

    public function isRefunded(): bool
    {
        return $this->status === TransactionStatus::Refunded;
    }

    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::Failed;
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::Completed;
    }

    /**
     * Mark a completed withdrawal as failed and dispatch TransactionFailed event.
     * The event listener will queue the reversal job.
     */
    public function fail(string $reason): bool
    {
        if ($this->status->isTerminal() && $this->status !== TransactionStatus::Completed) {
            return false;
        }

        $this->update([
            'status' => TransactionStatus::Failed,
            'failure_reason' => $reason,
        ]);

        TransactionFailed::dispatch($this, $reason);

        return true;
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

            $wallet->decrement('held_balance', $this->amount);

            $this->update([
                'status' => TransactionStatus::Voided,
                'notes' => $notes ?? $this->notes,
            ]);

            return true;
        });
    }
}
