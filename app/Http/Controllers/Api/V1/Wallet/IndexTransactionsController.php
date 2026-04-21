<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Enums\Wallets\WalletType;
use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
final class IndexTransactionsController
{
    #[Response([
        'data' => [
            ['id' => 1, 'amount' => '5000.00', 'type' => 'deposit', 'status' => 'completed', 'reference' => 'DEP-XXXXXXXXXX', 'notes' => null, 'created_at' => '2026-01-01T00:00:00.000000Z'],
        ],
        'links' => ['first' => '...', 'last' => '...', 'prev' => null, 'next' => null],
        'meta' => ['current_page' => 1, 'per_page' => 15, 'total' => 1],
    ], status: 200, description: 'Paginated transactions')]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $transactions = $request->user()
            ->getWallet(WalletType::General)
            ->transactions()
            ->latest()
            ->paginate(15);

        return TransactionResource::collection($transactions);
    }
}
