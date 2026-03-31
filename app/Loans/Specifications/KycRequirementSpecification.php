<?php

namespace App\Loans\Specifications;

use App\Enums\Kyc\KycType;
use App\Models\User;

class KycRequirementSpecification implements LoanSpecification
{
    public function __construct(private float $requestedAmount) {}

    public function isSatisfiedBy(User $user): bool
    {
        // BOTH NIN and BVN are mandatory for any loan
        return $user->isKycVerified(KycType::Nin) && $user->isKycVerified(KycType::Bvn);
    }

    public function failureReason(): string
    {
        return 'You must complete both NIN and BVN verification before applying for a loan.';
    }
}
