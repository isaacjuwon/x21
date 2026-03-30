<?php

namespace App\Actions\Loans;

use App\Loans\EligibilityResult;
use App\Loans\LoanEligibilityChecker;
use App\Loans\Specifications\LoanLevelSpecification;
use App\Loans\Specifications\SharesRequirementSpecification;
use App\Loans\Specifications\UserDurationSpecification;
use App\Loans\Specifications\KycRequirementSpecification;
use App\Models\User;
use App\Settings\LoanSettings;

class CheckLoanEligibilityAction
{
    public function handle(User $user, float $amount): EligibilityResult
    {
        $settings = app(LoanSettings::class);

        $specifications = [];

        if ($settings->enforce_min_account_age) {
            $specifications[] = new UserDurationSpecification($settings->min_account_age_days);
        }

        if ($settings->enforce_loan_level_limits) {
            $specifications[] = new LoanLevelSpecification($amount);
        }

        if ($settings->enforce_shares_requirement) {
            $specifications[] = new SharesRequirementSpecification($amount);
        }

        if ($settings->enforce_kyc_requirement) {
            $specifications[] = new KycRequirementSpecification($amount);
        }

        $checker = new LoanEligibilityChecker($specifications);

        return $checker->check($user);
    }
}
