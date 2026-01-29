<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Loans\ApplyForLoanAction;
use App\Actions\Loans\MakeLoanPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Loan\ApplyLoanRequest;
use App\Http\Requests\Api\Loan\RepayLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * List user's loans.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $loans = Loan::where('user_id', $request->user()->id)
            ->with('loanLevel')
            ->latest()
            ->paginate(20);

        return LoanResource::collection($loans);
    }

    /**
     * Apply for a new loan.
     */
    public function apply(ApplyLoanRequest $request, ApplyForLoanAction $action): LoanResource
    {
        $payload = $request->payload();

        // Wrap execution in transaction to follow guidelines
        // We use execute() because the action class is existing and must not be modified.
        $loan = DB::transaction(function () use ($action, $request, $payload) {
            return $action->execute($request->user(), $payload->amount);
        });

        return new LoanResource($loan);
    }

    /**
     * Make a loan repayment.
     */
    public function repay(RepayLoanRequest $request, Loan $loan, MakeLoanPaymentAction $action): \Illuminate\Http\JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== $request->user()->id) {
            abort(403);
        }

        $payload = $request->payload();

        DB::transaction(function () use ($action, $loan, $payload) {
            // We use execute() because the action class is existing and must not be modified.
            // Check signature: execute(Loan $loan, float $amount)
            $action->execute($loan, $payload->amount);
        });

        return response()->json(['message' => 'Loan repayment processed successfully.']);
    }
}
