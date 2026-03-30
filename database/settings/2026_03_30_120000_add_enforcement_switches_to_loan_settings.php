<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('loans.enforce_min_account_age', true);
        $this->migrator->add('loans.enforce_loan_level_limits', true);
        $this->migrator->add('loans.enforce_shares_requirement', true);
        $this->migrator->add('loans.enforce_kyc_requirement', true);
    }
};
