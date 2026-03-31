<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Enums\Wallets\WalletType;
use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class TransactionController
{
    #[Response([
        'data' => [
            ['id' => 1, 'amount' => '5000.00', 'type' => 'deposit', 'status' => 'completed', 'reference' => 'DEP-XXXXXXXXXX', 'notes' => null, 'created_at' => '2026-01-01T00:00:00.000000Z'],
        ],
        'links' => ['first' => '...', 'last' => '...', 'prev' => null, 'next' => null],
        'meta' => ['current_page' => 1, 'per_page' => 15, 'total' => 1],
    ], status: 200, description: 'Paginated transactions')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = $request->user()
            ->getWallet(WalletType::General)
            ->transactions()
            ->latest()
            ->paginate(15);

        return TransactionResource::collection($transactions);
    }

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
    public function show(Request $request, Transaction $transaction): TransactionResource
    {
        abort_unless($transaction->wallet->user_id === $request->user()->id, 404);

        return new TransactionResource($transaction);
    }
}
