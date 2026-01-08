<?php

namespace App\Actions\Wallet;

use App\Enums\WalletType;
use App\Events\Wallet\WalletTransferred;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\User;
use App\Support\Result;
use Exception;
use Illuminate\Support\Facades\Log;

class TransferFundAction
{
    public function handle(User $sender, array $data): Result
    {
        try {
            // Find recipient by phone number
            $recipient = User::where('phone_number', $data['phone_number'])->first();

            if (! $recipient) {
                throw new Exception('Recipient not found with the provided phone number.');
            }

            if ($sender->is($recipient)) {
                throw new Exception('You cannot transfer funds to yourself.');
            }

            // Use the HandlesTransfer trait
            $walletType = WalletType::Main;

            $sender->transfer(
                recipient: $recipient,
                amount: $data['amount'],
                type: $walletType,
                notes: $data['notes'] ?? null
            );

            // Dispatch event for both sender and recipient
            WalletTransferred::dispatch($sender, $recipient, $data['amount'], $walletType, $data['notes'] ?? null);

            return Result::ok([
                'message' => 'Transfer completed successfully.',
                'recipient_name' => $recipient->name,
                'amount' => $data['amount'],
            ]);

        } catch (InsufficientBalanceException $e) {
            Log::warning('Transfer Failed - Insufficient Balance: '.$e->getMessage(), ['sender' => $sender->id, 'data' => $data]);

            return Result::error($e);
        } catch (Exception $e) {
            Log::error('Transfer Failed: '.$e->getMessage(), ['sender' => $sender->id, 'data' => $data, 'exception' => $e]);

            return Result::error($e);
        }
    }

    /**
     * Validate recipient by phone number
     */
    public function validateRecipient(string $phoneNumber): Result
    {
        try {
            $recipient = User::where('phone_number', $phoneNumber)->first();

            if (! $recipient) {
                return Result::error(new Exception('No user found with this phone number.'));
            }

            return Result::ok([
                'name' => $recipient->name,
                'phone_number' => $recipient->phone_number,
            ]);

        } catch (Exception $e) {
            Log::error('Recipient Validation Failed: '.$e->getMessage(), ['phone_number' => $phoneNumber]);

            return Result::error($e);
        }
    }
}
