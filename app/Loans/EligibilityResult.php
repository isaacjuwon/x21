<?php

namespace App\Loans;

use App\Loans\Specifications\LoanSpecification;

readonly class EligibilityResult
{
    public function __construct(
        public bool $passed,
        public ?LoanSpecification $failingSpecification,
    ) {}
}
