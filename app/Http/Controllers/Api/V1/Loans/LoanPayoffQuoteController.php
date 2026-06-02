<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\CalculateLoanPayoffAction;
use App\Models\Loan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
class LoanPayoffQuoteController
{
    use AuthorizesRequests;

    #[Response([
        'data' => [
            'remaining_principal' => 5000.00,
            'accrued_interest' => 150.25,
            'prepayment_penalty' => 75.00,
            'total_payoff_amount' => 5225.25,
        ],
    ], status: 200)]
    public function __invoke(Loan $loan, CalculateLoanPayoffAction $action): JsonResponse
    {
        $this->authorize('view', $loan);

        $quote = $action->handle($loan);

        return response()->json([
            'data' => $quote,
        ]);
    }
}
