<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('loans.min_amount', 1000.0);
        $this->migrator->add('loans.max_amount', 100000.0);
        $this->migrator->add('loans.default_interest_rate', 5.0);
        $this->migrator->add('loans.auto_approve', false);

        $this->migrator->add('shares.price_per_share', 10.0);
        $this->migrator->add('shares.min_shares_purchase', 100);
        $this->migrator->add('shares.max_shares_per_user', 10000);
    }
};
