<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Enums\Wallets\WalletType;
use App\Http\Resources\Api\V1\Wallet\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
final class IndexTransactionsController
{
    #[QueryParam('type', 'string', description: 'Filter by transaction type (deposit, withdrawal, transfer, hold, release)', required: false, example: 'deposit')]
    #[QueryParam('status', 'string', description: 'Filter by status (pending, completed, failed, reversed)', required: false, example: 'completed')]
    #[QueryParam('search', 'string', description: 'Search by reference', required: false, example: 'WFT-ABC')]
    #[QueryParam('sort_by', 'string', description: 'Sort column: created_at or amount', required: false, example: 'created_at')]
    #[QueryParam('sort_direction', 'string', description: 'Sort direction: asc or desc', required: false, example: 'desc')]
    #[QueryParam('per_page', 'integer', description: 'Results per page (max 100)', required: false, example: 15)]
    #[Response([
        'data' => [
            ['id' => 1, 'amount' => '5000.00', 'type' => 'deposit', 'status' => 'completed', 'reference' => 'WFT-XXXXXXXXXX', 'notes' => null, 'failure_reason' => null, 'performed_by' => null, 'created_at' => '2026-01-01T00:00:00.000000Z'],
        ],
        'links' => ['first' => '...', 'last' => '...', 'prev' => null, 'next' => null],
        'meta' => ['current_page' => 1, 'per_page' => 15, 'total' => 1],
    ], status: 200, description: 'Paginated transactions')]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:created_at,amount'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';
        $perPage = $validated['per_page'] ?? 15;

        $transactions = $request->user()
            ->getWallet(WalletType::General)
            ->transactions()
            ->with('performedByUser:id,name')
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['search'] ?? null, fn ($q, $search) => $q->where('reference', 'like', "%{$search}%"))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);

        return TransactionResource::collection($transactions);
    }
}
