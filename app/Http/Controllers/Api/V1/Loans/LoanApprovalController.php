<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\ApproveLoanAction;
use App\Http\Requests\Api\V1\Loans\StoreApprovalRequest;
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
class LoanApprovalController
{
    use AuthorizesRequests;

    #[BodyParam('notes', 'string', description: 'Optional approval notes', required: false, example: 'Approved after review')]

    #[ResponseFromApiResource(LoanResource::class, Loan::class)]
    #[Response(['message' => 'Loan must be in active status to be approved.'], status: 422)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(StoreApprovalRequest $request, Loan $loan, ApproveLoanAction $action): LoanResource
    {
        $this->authorize('approve', $loan);

        return new LoanResource(
            $action->handle($loan, $request->user(), $request->notes)
        );
    }
}
