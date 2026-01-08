<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('wallet.wallet_enabled', true);
    }

    public function down(): void
    {
        $this->migrator->delete('wallet.wallet_enabled');
    }
};
