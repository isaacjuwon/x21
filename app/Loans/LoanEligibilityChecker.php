<?php

namespace App\Loans;

use App\Loans\Specifications\LoanSpecification;
use App\Models\User;

class LoanEligibilityChecker
{
    /**
     * @param  array<LoanSpecification>  $specifications
     */
    public function __construct(private array $specifications) {}

    public function check(User $user): EligibilityResult
    {
        foreach ($this->specifications as $spec) {
            if (! $spec->isSatisfiedBy($user)) {
                return new EligibilityResult(false, $spec);
            }
        }

        return new EligibilityResult(true, null);
    }
}
