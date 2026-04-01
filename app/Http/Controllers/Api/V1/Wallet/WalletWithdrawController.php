<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Actions\Wallets\WithdrawWalletAction;
use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class WalletWithdrawController
{
    #[BodyParam('amount', 'number', description: 'Amount to withdraw', required: true, example: 5000)]
    #[BodyParam('account_number', 'string', description: 'Bank account number', required: true, example: '0123456789')]
    #[BodyParam('bank_code', 'string', description: 'Paystack bank code', required: true, example: '058')]
    #[BodyParam('bank_name', 'string', description: 'Bank name for display', required: true, example: 'GTBank')]
    #[Response([
        'data' => [
            'id' => 1,
            'amount' => '5050.00',
            'type' => 'withdrawal',
            'status' => 'completed',
            'reference' => 'WDR-XXXXXXXXXX',
            'notes' => 'Withdrawal to John Doe (GTBank)',
            'created_at' => '2026-01-01T00:00:00.000000Z',
        ],
    ], status: 201, description: 'Withdrawal initiated')]
    #[Response(['message' => 'Insufficient funds.'], status: 422)]
    public function __invoke(Request $request, WithdrawWalletAction $action): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'account_number' => ['required', 'string', 'digits:10'],
            'bank_code' => ['required', 'string'],
            'bank_name' => ['required', 'string'],
        ]);

        $transaction = $action->handle(
            user: $request->user(),
            amount: (float) $validated['amount'],
            accountNumber: $validated['account_number'],
            bankCode: $validated['bank_code'],
            bankName: $validated['bank_name'],
        );

        return (new TransactionResource($transaction))->response()->setStatusCode(201);
    }
}
