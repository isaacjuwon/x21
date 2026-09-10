<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Actions\Wallets\InitializeWalletFundingAction;
use App\Actions\Wallets\VerifyWalletFundingAction;
use App\Enums\Wallets\WalletType;
use App\Http\Requests\Api\V1\Wallet\InitializeWalletFundRequest;
use App\Http\Requests\Api\V1\Wallet\VerifyWalletFundRequest;
use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class WalletFundController
{
    #[BodyParam('amount', 'number', description: 'Amount to fund in the smallest currency unit (min: 100)', required: true, example: 5000)]
    #[Response([
        'data' => [
            'authorization_url' => 'https://checkout.paystack.com/abc123',
            'reference' => 'WFT-XXXXXXXXXX',
        ],
    ], status: 200, description: 'Paystack authorization URL')]
    #[Response(['message' => 'The amount field must be at least 100.'], status: 422)]
    public function initialize(InitializeWalletFundRequest $request, InitializeWalletFundingAction $action): JsonResponse
    {
        $authorizationUrl = $action->handle($request->user(), (float) $request->amount);

        return response()->json([
            'data' => [
                'authorization_url' => $authorizationUrl,
                'reference' => $request->user()
                    ->getWallet(WalletType::General)
                    ->transactions()
                    ->latest()
                    ->value('reference'),
            ],
        ]);
    }

    #[BodyParam('reference', 'string', description: 'The Paystack payment reference from the callback URL', required: true, example: 'WFT-XXXXXXXXXX')]
    #[Response([
        'data' => [
            'id' => 1,
            'amount' => '5000.00',
            'type' => 'deposit',
            'status' => 'completed',
            'reference' => 'WFT-XXXXXXXXXX',
            'notes' => 'Wallet funding via Paystack',
            'created_at' => '2026-01-01T00:00:00.000000Z',
        ],
    ], status: 200, description: 'Verified transaction')]
    #[Response(['message' => 'Not Found'], status: 404)]
    public function verify(VerifyWalletFundRequest $request, VerifyWalletFundingAction $action): TransactionResource
    {
        $transaction = $action->handle($request->reference);

        return new TransactionResource($transaction);
    }
}
