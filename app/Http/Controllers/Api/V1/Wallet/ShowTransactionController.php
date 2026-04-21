<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
final class ShowTransactionController
{
    #[Response([
        'data' => [
            'id' => 1,
            'amount' => '5000.00',
            'type' => 'deposit',
            'status' => 'completed',
            'reference' => 'DEP-XXXXXXXXXX',
            'notes' => null,
            'created_at' => '2026-01-01T00:00:00.000000Z',
        ],
    ], status: 200)]
    #[Response(['message' => 'Not Found'], status: 404)]
    public function __invoke(Request $request, Transaction $transaction): TransactionResource
    {
        abort_unless($transaction->wallet->user_id === $request->user()->id, 404);

        return new TransactionResource($transaction);
    }
}
