<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\RejectLoanAction;
use App\Http\Requests\Api\V1\Loans\StoreRejectionRequest;
use App\Http\Resources\Api\V1\Loans\LoanResource;
use App\Models\Loan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
class LoanRejectionController
{
    use AuthorizesRequests;

    #[BodyParam('rejection_reason', 'string', description: 'Reason for rejecting the loan', required: true, example: 'Insufficient income documentation')]
    #[BodyParam('notes', 'string', description: 'Optional internal notes', required: false, example: 'Reviewed by compliance team')]

    #[ResponseFromApiResource(LoanResource::class, Loan::class)]
    #[Response(['message' => 'Loan cannot be rejected in its current status.'], status: 422)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(StoreRejectionRequest $request, Loan $loan, RejectLoanAction $action): LoanResource
    {
        $this->authorize('reject', $loan);

        return new LoanResource(
            $action->handle($loan, $request->user(), $request->rejection_reason, $request->notes)
        );
    }
}
