<?php

namespace App\Actions\Wallet;

use App\Enums\Connectors\PaymentConnector;
use App\Enums\Transaction\Status;
use App\Enums\Transaction\Type;
use App\Integrations\Paystack\Entities\InitializePayment;
use App\Integrations\Paystack\Entities\PaymentResponse;
use App\Models\Transaction;
use App\Support\Result;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FundWalletAction
{
    public function handle(array $data): Result
    {
        try {
            $user = Auth::user();

            // Create pending transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => Type::Deposit,
                'status' => Status::Pending,
                'reference' => $data['reference'],
                'meta' => [
                    'service' => 'wallet_funding',
                    'email' => $data['email'],
                ],
            ]);

            $payload = new InitializePayment(
                email: $data['email'],
                amount: $data['amount'],
                reference: $data['reference'],
                callbackUrl: route('wallet.callback'),
                metadata: ['transaction_id' => $transaction->id, 'type' => 'wallet_funding'],
            );

            /** @var PaymentResponse $result */
            $result = PaymentConnector::default()->connector()->transactions()->initialize($payload);

            return Result::ok([
                'message' => 'Payment initialized.',
                'authorization_url' => $result->authorizationUrl,
                'access_code' => $result->accessCode,
                'reference' => $result->reference,
            ]);

        } catch (Exception $e) {
            Log::error('Fund Wallet Failed: '.$e->getMessage(), ['data' => $data, 'exception' => $e]);

            if (isset($transaction)) {
                $transaction->markAsFailed();
            }

            return Result::error($e);
        }
    }
}
