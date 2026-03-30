<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Loans\DisburseLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDisbursementRequest;
use App\Http\Resources\Api\V1\LoanResource;
use App\Models\Loan;

class LoanDisbursementController extends Controller
{
    public function store(StoreDisbursementRequest $request, Loan $loan, DisburseLoanAction $action): LoanResource
    {
        $this->authorize('disburse', $loan);

        $loan = $action->handle($loan, $request->user(), $request->notes);

        return new LoanResource($loan);
    }
}
