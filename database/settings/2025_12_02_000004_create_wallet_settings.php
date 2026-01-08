<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('wallet.min_funding_amount', 100);
        $this->migrator->add('wallet.max_funding_amount', 10000000);
        $this->migrator->add('wallet.min_withdrawal_amount', 1000);
        $this->migrator->add('wallet.max_withdrawal_amount', 1000000);
        $this->migrator->add('wallet.withdrawal_fee_percentage', 1.5);
        $this->migrator->add('wallet.withdrawal_fee_cap', 5000);
        $this->migrator->add('wallet.instant_withdrawal_enabled', true);
        $this->migrator->add('wallet.withdrawal_processing_hours', 24);
        $this->migrator->add('wallet.daily_withdrawal_limit', 500000);
        $this->migrator->add('wallet.monthly_withdrawal_limit', 5000000);
    }
};
