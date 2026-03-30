<?php

namespace App\Settings;

use App\Enums\Loans\InterestMethod;
use Spatie\LaravelSettings\Settings;

class LoanSettings extends Settings
{
    public float $min_amount;
    public float $max_amount;
    public float $default_interest_rate;
    public InterestMethod $interest_method;
    public float $min_shares_percentage;
    public bool $auto_approve;
    public bool $enforce_min_account_age;
    public bool $enforce_loan_level_limits;
    public bool $enforce_shares_requirement;
    public bool $enforce_kyc_requirement;
    public int $min_account_age_days;

    public static function group(): string
    {
        return 'loans';
    }
}
