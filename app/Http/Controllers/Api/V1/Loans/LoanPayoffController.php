<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\PayoffLoanAction;
use App\Http\Resources\Api\V1\Loans\LoanRepaymentResource;
use App\Models\Loan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
class LoanPayoffController
{
    use AuthorizesRequests;

    #[Response(['message' => 'Insufficient funds.'], status: 422)]
    public function __invoke(Loan $loan, PayoffLoanAction $action): JsonResponse
    {
        $this->authorize('view', $loan);

        $repayment = $action->handle($loan, auth()->user());

        return (new LoanRepaymentResource($repayment))->response()->setStatusCode(201);
    }
}
