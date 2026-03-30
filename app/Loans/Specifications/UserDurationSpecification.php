<?php

namespace App\Loans\Specifications;

use App\Models\User;

class UserDurationSpecification implements LoanSpecification
{
    public function __construct(private int $thresholdDays) {}

    public function isSatisfiedBy(User $user): bool
    {
        return $user->created_at->diffInDays(now()) >= $this->thresholdDays;
    }

    public function failureReason(): string
    {
        return "Your account must be at least {$this->thresholdDays} days old to apply for a loan.";
    }
}
