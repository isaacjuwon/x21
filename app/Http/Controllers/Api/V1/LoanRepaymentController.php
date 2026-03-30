<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Loans\RepayLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRepaymentRequest;
use App\Http\Resources\Api\V1\LoanRepaymentResource;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;

class LoanRepaymentController extends Controller
{
    public function store(StoreRepaymentRequest $request, Loan $loan, RepayLoanAction $action): JsonResponse
    {
        $this->authorize('view', $loan);

        $repayment = $action->handle($loan, $request->user(), (float) $request->amount);

        return (new LoanRepaymentResource($repayment))->response()->setStatusCode(201);
    }
}
