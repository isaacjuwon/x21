<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('integrations.vtugate_url')) {
            $this->migrator->add('integrations.vtugate_url', 'https://api.vtugate.com');
        }

        if (! $this->migrator->exists('integrations.vtugate_api_key')) {
            $this->migrator->add('integrations.vtugate_api_key', '');
        }
    }
};
