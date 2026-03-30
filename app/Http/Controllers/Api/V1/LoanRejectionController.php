<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Loans\RejectLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRejectionRequest;
use App\Http\Resources\Api\V1\LoanResource;
use App\Models\Loan;

class LoanRejectionController extends Controller
{
    public function store(StoreRejectionRequest $request, Loan $loan, RejectLoanAction $action): LoanResource
    {
        $this->authorize('reject', $loan);

        $loan = $action->handle($loan, $request->user(), $request->rejection_reason, $request->notes);

        return new LoanResource($loan);
    }
}
