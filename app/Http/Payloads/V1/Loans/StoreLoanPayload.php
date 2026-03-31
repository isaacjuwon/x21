<?php

namespace App\Http\Payloads\V1\Loans;

use App\Enums\Loans\InterestMethod;

final readonly class StoreLoanPayload
{
    public function __construct(
        public float $principalAmount,
        public int $repaymentTermMonths,
        public InterestMethod $interestMethod,
        public ?string $notes,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            principalAmount: (float) $data['principal_amount'],
            repaymentTermMonths: (int) $data['repayment_term_months'],
            interestMethod: InterestMethod::from($data['interest_method'] ?? InterestMethod::FlatRate->value),
            notes: $data['notes'] ?? null,
        );
    }
}
