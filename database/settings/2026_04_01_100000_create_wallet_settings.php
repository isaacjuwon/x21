<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('wallet.min_withdrawal', 500.0);
        $this->migrator->add('wallet.withdrawal_fee', 50.0);
        $this->migrator->add('wallet.stamp_duty_rate', 0.005); // 0.5%
        $this->migrator->add('wallet.stamp_duty_threshold', 10000.0);
    }
};
