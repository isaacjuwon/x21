<?php

namespace App\Loans\Specifications;

use App\Enums\Kyc\KycType;
use App\Models\User;

class KycRequirementSpecification implements LoanSpecification
{
    public function __construct(private float $requestedAmount) {}

    public function isSatisfiedBy(User $user): bool
    {
        // Simple verification (NIN) is always required for any loan
        if (! $user->isKycVerified(KycType::Nin)) {
            return false;
        }

        // Advanced verification (BVN) is required for loans above a certain threshold
        // For now, let's assume loans above 50,000 require BVN (can be moved to settings later)
        if ($this->requestedAmount > 50000 && ! $user->isKycVerified(KycType::Bvn)) {
            return false;
        }

        return true;
    }

    public function failureReason(): string
    {
        return 'You must complete NIN verification for all loans, and BVN verification for loans above ₦50,000.';
    }
}
