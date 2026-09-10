<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Enums\Wallets\WalletType;
use App\Http\Requests\Api\V1\Wallet\WalletTransferRequest;
use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class WalletTransferController
{
    #[BodyParam('recipient_email', 'string', description: 'Email address of the recipient', required: true, example: 'recipient@example.com')]
    #[BodyParam('amount', 'number', description: 'Amount to transfer (min: 1)', required: true, example: 1000)]
    #[BodyParam('notes', 'string', description: 'Optional transfer note', required: false, example: 'Payment for services')]
    #[Response([
        'data' => [
            'id' => 1,
            'amount' => '1000.00',
            'type' => 'withdrawal',
            'status' => 'completed',
            'reference' => 'WTH-XXXXXXXXXX',
            'notes' => 'Transfer to recipient@example.com',
            'created_at' => '2026-01-01T00:00:00.000000Z',
        ],
    ], status: 201, description: 'Transfer initiated')]
    #[Response(['message' => 'Insufficient funds.'], status: 422)]
    #[Response(['message' => 'Recipient not found.'], status: 404)]
    public function __invoke(WalletTransferRequest $request): JsonResponse
    {
        $recipient = User::where('email', $request->recipient_email)->firstOrFail();

        $transactions = $request->user()->transfer(
            amount: (float) $request->amount,
            recipient: $recipient,
            type: WalletType::General,
            notes: $request->notes,
        );

        return (new TransactionResource($transactions['sender']))->response()->setStatusCode(201);
    }
}
