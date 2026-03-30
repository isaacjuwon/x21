<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLoanRequest;
use App\Http\Resources\Api\V1\LoanResource;
use App\Models\Loan;
use App\Enums\Loans\LoanStatus;
use App\Settings\LoanSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LoanController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Loan::class);

        return LoanResource::collection(
            $request->user()->loans()->latest()->paginate(15)
        );
    }

    public function store(StoreLoanRequest $request): JsonResponse
    {
        $this->authorize('create', Loan::class);

        $settings = app(LoanSettings::class);

        $loan = $request->user()->loans()->create([
            'principal_amount' => $request->principal_amount,
            'outstanding_balance' => $request->principal_amount,
            'interest_rate' => $request->user()->loanLevel?->interest_rate ?? $settings->default_interest_rate,
            'repayment_term_months' => $request->repayment_term_months,
            'interest_method' => $request->input('interest_method', 'FlatRate'),
            'status' => LoanStatus::Active,
            'eligibility_checked_at' => now(),
            'eligibility_passed' => true,
        ]);

        return (new LoanResource($loan))->response()->setStatusCode(201);
    }

    public function show(Request $request, Loan $loan): LoanResource
    {
        $this->authorize('view', $loan);

        return new LoanResource($loan);
    }
}
