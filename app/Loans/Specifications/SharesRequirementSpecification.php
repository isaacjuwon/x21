<?php

namespace App\Loans\Specifications;

use App\Models\User;
use App\Settings\LoanSettings;
use App\Settings\ShareSettings;
use Illuminate\Support\Number;

class SharesRequirementSpecification implements LoanSpecification
{
    public function __construct(private float $requestedAmount) {}

    public function isSatisfiedBy(User $user): bool
    {
        $loanSettings = app(LoanSettings::class);
        $shareSettings = app(ShareSettings::class);

        $requiredValue = $this->requestedAmount * ($loanSettings->min_shares_percentage / 100);
        $userSharesQuantity = $user->shareHolding?->quantity ?? 0;
        $userSharesValue = $userSharesQuantity * $shareSettings->price_per_share;

        return $userSharesValue >= $requiredValue;
    }

    public function failureReason(): string
    {
        $loanSettings = app(LoanSettings::class);
        $shareSettings = app(ShareSettings::class);

        $requiredValue = $this->requestedAmount * ($loanSettings->min_shares_percentage / 100);

        return sprintf(
            'You are required to have at least %s worth of shares (%.0f%% of the requested loan amount) to be eligible.',
            Number::currency($requiredValue),
            $loanSettings->min_shares_percentage
        );
    }
}
