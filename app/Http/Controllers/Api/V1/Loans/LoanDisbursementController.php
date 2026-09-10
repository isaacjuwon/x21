<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\DisburseLoanAction;
use App\Http\Requests\Api\V1\Loans\StoreDisbursementRequest;
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
class LoanDisbursementController
{
    use AuthorizesRequests;

    #[BodyParam('notes', 'string', description: 'Optional disbursement notes', required: false, example: 'Disbursed to account')]

    #[ResponseFromApiResource(LoanResource::class, Loan::class)]
    #[Response(['message' => 'Loan must be in approved status to be disbursed.'], status: 422)]
    #[Response(['message' => 'This action is unauthorized.'], status: 403)]
    public function __invoke(StoreDisbursementRequest $request, Loan $loan, DisburseLoanAction $action): LoanResource
    {
        $this->authorize('disburse', $loan);

        return new LoanResource(
            $action->handle($loan, $request->user(), $request->notes)
        );
    }
}
