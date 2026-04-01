<?php

namespace App\Actions\Wallets;

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use App\Enums\Wallets\WalletType;
use App\Exceptions\Wallets\InsufficientFundsException;
use App\Integrations\Paystack\Entities\CreateRecipient;
use App\Integrations\Paystack\Entities\InitiateTransfer;
use App\Integrations\Paystack\PaystackConnector;
use App\Models\Transaction;
use App\Models\User;
use App\Settings\WalletSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WithdrawWalletAction
{
    public function __construct(
        private readonly PaystackConnector $paystack,
        private readonly WalletSettings $settings,
    ) {}

    /**
     * Calculate the total debit amount including fee and stamp duty.
     *
     * @return array{amount: float, fee: float, stamp_duty: float, total: float}
     */
    public function calculateCharges(float $amount): array
    {
        $fee = $this->settings->withdrawal_fee;
        $stampDuty = $amount >= $this->settings->stamp_duty_threshold
            ? round($amount * $this->settings->stamp_duty_rate, 2)
            : 0.0;

        return [
            'amount' => $amount,
            'fee' => $fee,
            'stamp_duty' => $stampDuty,
            'total' => round($amount + $fee + $stampDuty, 2),
        ];
    }

    /**
     * Verify account name matches user's full name (case-insensitive partial match).
     */
    public function verifyAccountName(string $accountNumber, string $bankCode, User $user): string
    {
        $bankAccount = $this->paystack->bank()->resolve($accountNumber, $bankCode);

        $resolvedName = strtolower(trim($bankAccount->accountName));
        $userName = strtolower(trim($user->name));

        // Check if any word in the user's name appears in the resolved account name
        $nameParts = array_filter(explode(' ', $userName));
        $matchCount = count(array_filter($nameParts, fn ($part) => str_contains($resolvedName, $part)));

        if ($matchCount === 0) {
            throw ValidationException::withMessages([
                'account_number' => "Account name '{$bankAccount->accountName}' does not match your registered name.",
            ]);
        }

        return $bankAccount->accountName;
    }

    /**
     * Execute the full withdrawal flow.
     */
    public function handle(User $user, float $amount, string $accountNumber, string $bankCode, string $bankName): Transaction
    {
        if ($amount < $this->settings->min_withdrawal) {
            throw ValidationException::withMessages([
                'amount' => "Minimum withdrawal amount is {$this->settings->min_withdrawal}.",
            ]);
        }

        $charges = $this->calculateCharges($amount);

        $wallet = $user->getWallet(WalletType::General);

        if ($wallet->available_balance < $charges['total']) {
            throw new InsufficientFundsException;
        }

        // Verify account name against user's name
        $accountName = $this->verifyAccountName($accountNumber, $bankCode, $user);

        return DB::transaction(function () use ($user, $amount, $charges, $accountNumber, $bankCode, $bankName, $accountName) {
            // Debit the full amount (including fee + stamp duty) from wallet
            $wallet = $user->getWallet(WalletType::General);
            $wallet->decrement('balance', $charges['total']);

            $reference = 'WDR-'.strtoupper(Str::random(10));

            $transaction = $wallet->transactions()->create([
                'amount' => $charges['total'],
                'type' => TransactionType::Withdrawal,
                'status' => TransactionStatus::Pending,
                'reference' => $reference,
                'notes' => "Withdrawal to {$accountName} ({$bankName})",
                'meta' => [
                    'amount' => $charges['amount'],
                    'fee' => $charges['fee'],
                    'stamp_duty' => $charges['stamp_duty'],
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                    'bank_name' => $bankName,
                    'account_name' => $accountName,
                ],
            ]);

            // Create Paystack transfer recipient
            $recipient = $this->paystack->recipients()->create(new CreateRecipient(
                name: $accountName,
                accountNumber: $accountNumber,
                bankCode: $bankCode,
            ));

            // Initiate Paystack transfer (amount in kobo)
            $transfer = $this->paystack->transfers()->initiate(new InitiateTransfer(
                source: 'balance',
                amount: (int) ($charges['amount'] * 100), // kobo
                recipient: $recipient->recipientCode,
                reference: $reference,
                reason: "Wallet withdrawal for {$user->name}",
            ));

            // Update transaction with transfer code
            $transaction->update([
                'status' => TransactionStatus::Completed,
                'meta' => array_merge($transaction->meta ?? [], [
                    'transfer_code' => $transfer->transferCode ?? null,
                    'paystack_status' => $transfer->status ?? null,
                ]),
            ]);

            return $transaction->fresh();
        });
    }
}
