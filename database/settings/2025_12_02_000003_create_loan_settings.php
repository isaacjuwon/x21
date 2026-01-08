<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('loans.min_loan_amount', 5000);
        $this->migrator->add('loans.max_loan_amount', 5000000);
        $this->migrator->add('loans.default_interest_rate', 10.0);
        $this->migrator->add('loans.min_loan_duration_months', 1);
        $this->migrator->add('loans.max_loan_duration_months', 60);
        $this->migrator->add('loans.auto_approval_threshold', 50000);
        $this->migrator->add('loans.require_guarantor', true);
        $this->migrator->add('loans.min_guarantors', 1);
        $this->migrator->add('loans.late_payment_penalty_percentage', 2.5);
        $this->migrator->add('loans.grace_period_days', 7);
        $this->migrator->add('loans.loan_to_shares_ratio', 0.15);
    }
};
