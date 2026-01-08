<?php

namespace App\Actions\Wallet;

use App\Enums\Connectors\PaymentConnector;
use App\Integrations\Paystack\Entities\InitiateTransfer;
use App\Integrations\Paystack\Entities\CreateRecipient;
use App\Integrations\Paystack\Entities\TransferResponse;
use App\Models\Transaction;
use App\Enums\Transaction\Type;
use App\Enums\Transaction\Status;
use Illuminate\Support\Facades\Auth;
use App\Support\Result;
use Exception;
use Illuminate\Support\Facades\Log;

class WithdrawWalletAction
{
    public function handle(array $data): Result
    {
        try {
            $user = Auth::user();

            // 1. Resolve Account Details
            $account = PaymentConnector::default()->connector()->bank()->resolve(
                accountNumber: $data['account_number'],
                bankCode: $data['bank_code']
            );

            // 2. Verify Account Name matches User Name
            // Removing extra spaces and converting to uppercase for comparison
            $userName = strtoupper(trim($user->name));
            $accountName = strtoupper(trim($account->accountName));

            // Basic check: exact match or contains
            // Since bank names can be formatted differently (LAST FIRST MIDDLE), strict match might fail.
            // But the requirement is "user full name must match bank name".
            // I'll enforce strict match for now, or maybe allow if all parts of user name are in account name.
            
            // Let's try a slightly flexible check:
            // If the user's name is not contained in the account name (or vice versa), fail.
            // Actually, for security/compliance, usually it must match close enough.
            // I'll stick to a direct comparison for now as requested.
            
            if ($userName !== $accountName) {
                 // Fallback: check if the names are just in different order
                 $userParts = explode(' ', $userName);
                 $accountParts = explode(' ', $accountName);
                 sort($userParts);
                 sort($accountParts);
                 
                 if ($userParts !== $accountParts) {
                     return Result::error(new Exception("Account name ({$account->accountName}) does not match your profile name ({$user->name})."));
                 }
            }

            // 3. Create Recipient
            $recipient = PaymentConnector::default()->connector()->recipients()->create(
                new CreateRecipient(
                    name: $account->accountName,
                    accountNumber: $data['account_number'],
                    bankCode: $data['bank_code'],
                    currency: 'NGN'
                )
            );

            // 4. Create Pending Transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => Type::Withdrawal,
                'status' => Status::Pending,
                'reference' => $data['reference'],
                'recipient' => $recipient->recipientCode,
                'meta' => [
                    'service' => 'wallet_withdrawal',
                    'bank_code' => $data['bank_code'],
                    'account_number' => $data['account_number'],
                    'account_name' => $account->accountName,
                ],
            ]);

            // 5. Initiate Transfer
            $payload = new InitiateTransfer(
                source: 'balance',
                amount: (int) round($data['amount'] * 100), // Convert to kobo
                recipient: $recipient->recipientCode,
                reference: $data['reference'],
                reason: 'Wallet Withdrawal',
            );

            /** @var TransferResponse $result */
            $result = PaymentConnector::default()->connector()->transfers()->initiate($payload);

            // Update transaction status based on result
            if ($result->status === 'success' || $result->status === 'pending') {
                 // Note: Paystack transfers are often pending initially.
                 // We keep it as Pending until webhook confirms success.
                 return Result::ok([
                    'message' => 'Withdrawal initiated successfully.',
                    'data' => $result
                ]);
            }
            
            $transaction->markAsFailed();
            return Result::error(new Exception('Withdrawal failed: ' . $result->message));

        } catch (Exception $e) {
            Log::error('Withdraw Wallet Failed: ' . $e->getMessage(), ['data' => $data, 'exception' => $e]);
            
            if (isset($transaction)) {
                $transaction->markAsFailed();
            }

            return Result::error($e);
        }
    }
}
