<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('integrations.vtpass_url')) {
            $this->migrator->add('integrations.vtpass_url', 'https://vtpass.com/api');
        }

        if (! $this->migrator->exists('integrations.vtpass_api_key')) {
            $this->migrator->add('integrations.vtpass_api_key', '');
        }

        if (! $this->migrator->exists('integrations.vtpass_secret_key')) {
            $this->migrator->add('integrations.vtpass_secret_key', '');
        }

        if (! $this->migrator->exists('integrations.vtpass_public_key')) {
            $this->migrator->add('integrations.vtpass_public_key', '');
        }
    }
};
