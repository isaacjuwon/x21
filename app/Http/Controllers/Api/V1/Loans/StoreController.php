<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Enums\Loans\LoanStatus;
use App\Http\Requests\Api\V1\Loans\StoreLoanRequest;
use App\Http\Resources\Api\V1\Loans\LoanResource;
use App\Jobs\GenerateLoanScheduleJob;
use App\Models\Loan;
use App\Settings\LoanSettings;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
final class StoreController
{
    public function __construct(
        private readonly LoanSettings $settings,
    ) {}

    #[BodyParam('principal_amount', 'number', description: 'Loan amount requested (min: 1)', required: true, example: 50000)]
    #[BodyParam('repayment_term_months', 'integer', description: 'Repayment term in months (min: 1)', required: true, example: 12)]
    #[BodyParam('interest_method', 'string', description: 'Interest method: FlatRate or ReducingBalance', required: false, example: 'FlatRate')]
    #[BodyParam('notes', 'string', description: 'Purpose of the loan', required: false, example: 'Business expansion')]
    #[ResponseFromApiResource(LoanResource::class, Loan::class, status: 201)]
    #[Response(['message' => 'You are not eligible for this loan.'], status: 422, description: 'Eligibility failed')]
    public function __invoke(StoreLoanRequest $request): JsonResponse
    {
        $loan = $request->user()->loans()->create([
            'principal_amount' => $request->principal_amount,
            'outstanding_balance' => $request->principal_amount,
            'interest_rate' => $request->user()->loanLevel?->interest_rate ?? $this->settings->default_interest_rate,
            'repayment_term_months' => $request->repayment_term_months,
            'interest_method' => $request->input('interest_method', 'FlatRate'),
            'status' => LoanStatus::Active,
            'notes' => $request->notes,
            'eligibility_checked_at' => now(),
            'eligibility_passed' => true,
        ]);

        GenerateLoanScheduleJob::dispatch($loan);

        return (new LoanResource($loan))->response()->setStatusCode(201);
    }
}
