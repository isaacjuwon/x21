<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\CheckLoanEligibilityAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
class LoanEligibilityController
{
    #[BodyParam('principal_amount', 'number', required: true, example: 50000)]
    #[Response(['data' => ['passed' => true, 'reason' => null]], status: 200)]
    #[Response(['data' => ['passed' => false, 'reason' => 'Account must be at least 30 days old.']], status: 200)]
    public function __invoke(Request $request, CheckLoanEligibilityAction $action): JsonResponse
    {
        $request->validate([
            'principal_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $result = $action->handle($request->user(), (float) $request->principal_amount);

        return response()->json([
            'data' => [
                'passed' => $result->passed,
                'reason' => $result->passed ? null : $result->failingSpecification?->failureReason(),
            ],
        ]);
    }
}
