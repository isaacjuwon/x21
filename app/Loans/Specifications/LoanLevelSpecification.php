<?php

namespace App\Loans\Specifications;

use App\Models\User;
use App\Settings\LoanSettings;

class LoanLevelSpecification implements LoanSpecification
{
    public function __construct(private float $requestedAmount) {}

    public function isSatisfiedBy(User $user): bool
    {
        $settings = app(LoanSettings::class);
        $level = $user->loanLevel;

        $min = $level?->min_amount ?? $settings->min_amount;
        $max = $level?->max_amount ?? $settings->max_amount;

        return $this->requestedAmount >= $min && $this->requestedAmount <= $max;
    }

    public function failureReason(): string
    {
        $settings = app(LoanSettings::class);
        $user = auth()->user();
        $level = $user?->loanLevel;

        $min = $level?->min_amount ?? $settings->min_amount;
        $max = $level?->max_amount ?? $settings->max_amount;

        if ($this->requestedAmount < $min) {
            return "The minimum loan amount for your level is $" . number_format($min, 2) . ".";
        }

        return "The maximum loan amount for your level is $" . number_format($max, 2) . ".";
    }
}
