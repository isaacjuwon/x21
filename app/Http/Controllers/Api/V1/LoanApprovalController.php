<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Loans\ApproveLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreApprovalRequest;
use App\Http\Resources\Api\V1\LoanResource;
use App\Models\Loan;

class LoanApprovalController extends Controller
{
    public function store(StoreApprovalRequest $request, Loan $loan, ApproveLoanAction $action): LoanResource
    {
        $this->authorize('approve', $loan);

        $loan = $action->handle($loan, $request->user(), $request->notes);

        return new LoanResource($loan);
    }
}
