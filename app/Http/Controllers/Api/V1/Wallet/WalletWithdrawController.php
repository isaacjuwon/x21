<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Enums\Wallets\WalletType;
use App\Http\Requests\Api\V1\Wallet\WalletWithdrawRequest;
use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class WalletWithdrawController
{
    #[BodyParam('amount', 'number', description: 'Amount to withdraw (min: 1)', required: true, example: 2000)]
    #[BodyParam('notes', 'string', description: 'Optional withdrawal note', required: false, example: 'Monthly withdrawal')]
    #[Response([
        'data' => [
            'id' => 1,
            'amount' => '2000.00',
            'type' => 'withdrawal',
            'status' => 'completed',
            'reference' => 'WTH-XXXXXXXXXX',
            'notes' => 'Wallet withdrawal',
            'created_at' => '2026-01-01T00:00:00.000000Z',
        ],
    ], status: 201, description: 'Withdrawal initiated')]
    #[Response(['message' => 'Insufficient funds.'], status: 422)]
    public function __invoke(WalletWithdrawRequest $request): JsonResponse
    {
        $transaction = $request->user()->withdraw(
            amount: (float) $request->amount,
            type: WalletType::General,
            notes: $request->notes ?? 'Wallet withdrawal',
        );

        return (new TransactionResource($transaction))->response()->setStatusCode(201);
    }
}
