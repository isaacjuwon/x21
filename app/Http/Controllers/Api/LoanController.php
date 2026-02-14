<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Loans\ApplyForLoanAction;
use App\Actions\Loans\MakeLoanPaymentAction;
use App\Http\Requests\Api\Loan\ApplyLoanRequest;
use App\Http\Requests\Api\Loan\RepayLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends ApiController
{
    /**
     * List user's loans.
     */
    public function index(Request $request): JsonResponse
    {
        $loans = Loan::where('user_id', $request->user()->id)
            ->with('loanLevel')
            ->latest()
            ->paginate(20);

        $loans->setCollection($loans->getCollection()->mapInto(LoanResource::class));

        return $this->paginatedResponse($loans, 'Loans retrieved successfully');
    }

    /**
     * Apply for a new loan.
     */
    public function apply(ApplyLoanRequest $request, ApplyForLoanAction $action): JsonResponse
    {
        $payload = $request->payload();

        // Wrap execution in transaction to follow guidelines
        $loan = DB::transaction(function () use ($action, $request, $payload) {
            return $action->execute($request->user(), $payload->amount);
        });

        return $this->createdResponse(new LoanResource($loan), 'Loan application submitted successfully');
    }

    /**
     * Make a loan repayment.
     */
    public function repay(RepayLoanRequest $request, Loan $loan, MakeLoanPaymentAction $action): JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('You do not own this loan');
        }

        $payload = $request->payload();

        DB::transaction(function () use ($action, $loan, $payload) {
            $action->execute($loan, $payload->amount);
        });

        return $this->successResponse(null, 'Loan repayment processed successfully.');
    }
}
