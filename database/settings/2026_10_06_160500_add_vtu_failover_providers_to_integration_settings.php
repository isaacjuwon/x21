<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('integrations.vtu_failover_providers')) {
            $this->migrator->add('integrations.vtu_failover_providers', ['vtugate', 'vtpass']);
        }
    }
};
