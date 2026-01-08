<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shares.min_share_amount', 1000);
        $this->migrator->add('shares.max_share_amount', 1000000);
        $this->migrator->add('shares.share_interest_rate', 5.0);
        $this->migrator->add('shares.auto_approval_threshold', 10000);
        $this->migrator->add('shares.allow_multiple_currencies', true);
        $this->migrator->add('shares.min_shares_per_purchase', 1);
        $this->migrator->add('shares.max_shares_per_purchase', 100);
        $this->migrator->add('shares.require_admin_approval', true);
        $this->migrator->add('shares.share_price', 10.00);
    }
};
