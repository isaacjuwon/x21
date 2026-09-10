<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\RepayLoanAction;
use App\Http\Requests\Api\V1\Loans\StoreRepaymentRequest;
use App\Http\Resources\Api\V1\Loans\LoanRepaymentResource;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
class LoanRepaymentController
{
    use AuthorizesRequests;

    #[BodyParam('amount', 'number', description: 'Repayment amount (min: 0.01, max: outstanding balance)', required: true, example: 5000)]

    #[ResponseFromApiResource(LoanRepaymentResource::class, LoanRepayment::class, status: 201)]
    #[Response(['message' => 'Insufficient funds.'], status: 422)]
    public function __invoke(StoreRepaymentRequest $request, Loan $loan, RepayLoanAction $action): JsonResponse
    {
        $this->authorize('view', $loan);

        $repayment = $action->handle($loan, $request->user(), (float) $request->amount);

        return (new LoanRepaymentResource($repayment))->response()->setStatusCode(201);
    }
}
