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
     * Get user's loan eligibility status.
     */
    public function eligibility(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->loan_level_id) {
            return $this->successResponse([
                'shares_value' => $user->getSharesValue(),
                'eligible_amount' => 0,
                'loan_level_name' => null,
                'max_loan_amount' => 0,
                'installment_months' => 0,
                'interest_rate' => 0,
                'is_eligible' => false,
                'reason' => 'No loan level assigned to your account.',
            ]);
        }

        $loanLevel = $user->loanLevel;
        $sharesValue = $user->getSharesValue();
        $eligibleAmount = $user->getLoanEligibilityAmount();

        return $this->successResponse([
            'shares_value' => $sharesValue,
            'eligible_amount' => $eligibleAmount,
            'loan_level_name' => $loanLevel->name,
            'max_loan_amount' => $loanLevel->maximum_loan_amount,
            'installment_months' => $loanLevel->installment_period_months,
            'interest_rate' => $loanLevel->interest_rate,
            'is_eligible' => $eligibleAmount > 0 && ! $user->hasActiveLoan(),
            'has_active_loan' => $user->hasActiveLoan(),
        ]);
    }

    /**
     * Show detailed loan information.
     */
    public function show(Request $request, Loan $loan): JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('You do not own this loan');
        }

        $loan->load(['loanLevel', 'payments' => fn ($q) => $q->latest()->take(10)]);

        return $this->successResponse(new LoanResource($loan), 'Loan details retrieved successfully');
    }

    /**
     * Get loan payment schedule.
     */
    public function schedule(Request $request, Loan $loan, CalculateLoanScheduleAction $action): JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('You do not own this loan');
        }

        $schedule = $action->execute($loan);

        return $this->successResponse($schedule, 'Loan payment schedule retrieved successfully');
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
