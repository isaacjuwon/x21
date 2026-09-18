<?php

namespace App\Http\Controllers\Api\V1\Wallet;

use App\Actions\Wallets\WithdrawWalletAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Wallet', 'Wallet balance and overview')]
#[Authenticated]
class WithdrawalChargesController
{
    #[QueryParam('amount', 'number', description: 'Withdrawal amount to calculate charges for', required: true, example: 5000)]
    #[Response([
        'data' => [
            'amount' => 5000.00,
            'fee' => 50.00,
            'total' => 5050.00,
        ],
    ], status: 200, description: 'Breakdown of withdrawal charges')]
    #[Response(['message' => 'The amount field is required.'], status: 422)]
    public function __invoke(Request $request, WithdrawWalletAction $action): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $charges = $action->calculateCharges((float) $validated['amount']);

        return response()->json(['data' => $charges]);
    }
}
