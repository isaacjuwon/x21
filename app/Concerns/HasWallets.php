<?php

namespace App\Concerns;

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use App\Enums\Wallets\WalletType;
use App\Exceptions\Wallets\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasWallets
{
    /**
     * Boot the trait and register the created event to initialize wallets.
     */
    public static function bootHasWallets(): void
    {
        static::created(function ($model) {
            foreach (WalletType::cases() as $type) {
                $model->getWallet($type);
            }
        });
    }

    /**
     * Get all wallets for the user.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Get the default/general wallet for the user.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class)->where('type', WalletType::General);
    }

    /**
     * Get the general wallet attribute, ensuring it exists.
     */
    public function getWalletAttribute(): Wallet
    {
        return $this->getWallet(WalletType::General);
    }

    /**
     * Get or create a specific wallet.
     */
    public function getWallet(WalletType $type): Wallet
    {
        return $this->wallets()->firstOrCreate(
            ['type' => $type],
            ['balance' => 0, 'held_balance' => 0]
        );
    }

    /**
     * Deposit funds into a wallet.
     */
    public function deposit(float $amount, WalletType $type, ?string $notes = null): Transaction
    {
        return DB::transaction(function () use ($amount, $notes, $type) {
            $wallet = $this->getWallet($type);
            $wallet->increment('balance', $amount);

            return $wallet->transactions()->create([
                'amount' => $amount,
                'type' => TransactionType::Deposit,
                'status' => TransactionStatus::Completed,
                'reference' => 'DEP-'.strtoupper(Str::random(10)),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Immediate withdrawal (debit) from a wallet.
     */
    public function withdraw(float $amount, WalletType $type, ?string $notes = null): Transaction
    {
        return DB::transaction(function () use ($amount, $notes, $type) {
            $wallet = $this->getWallet($type);

            if ($wallet->available_balance < $amount) {
                throw new InsufficientFundsException;
            }

            $wallet->decrement('balance', $amount);

            return $wallet->transactions()->create([
                'amount' => $amount,
                'type' => TransactionType::Withdrawal,
                'status' => TransactionStatus::Completed,
                'reference' => 'WTH-'.strtoupper(Str::random(10)),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Hold funds in a wallet (Pending debit).
     */
    public function hold(float $amount, WalletType $type, ?string $notes = null): Transaction
    {
        return DB::transaction(function () use ($amount, $notes, $type) {
            $wallet = $this->getWallet($type);

            if ($wallet->available_balance < $amount) {
                throw new InsufficientFundsException;
            }

            $wallet->increment('held_balance', $amount);

            return $wallet->transactions()->create([
                'amount' => $amount,
                'type' => TransactionType::Hold,
                'status' => TransactionStatus::Pending,
                'reference' => 'HLD-'.strtoupper(Str::random(10)),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Transfer funds to another user's wallet.
     */
    public function transfer(float $amount, self $recipient, WalletType $type, ?string $notes = null): array
    {
        return DB::transaction(function () use ($amount, $recipient, $type, $notes) {
            $senderTransaction = $this->withdraw($amount, $type, $notes ?? "Transfer to {$recipient->name}");
            $recipientTransaction = $recipient->deposit($amount, $type, $notes ?? "Transfer from {$this->name}");

            return [
                'sender' => $senderTransaction,
                'recipient' => $recipientTransaction,
            ];
        });
    }
}
