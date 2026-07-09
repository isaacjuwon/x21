<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('integrations.kudisms_url')) {
            $this->migrator->add('integrations.kudisms_url', 'https://api.kudisms.net');
        }

        if (! $this->migrator->exists('integrations.kudisms_api_key')) {
            $this->migrator->add('integrations.kudisms_api_key', '');
        }

        if (! $this->migrator->exists('integrations.kudisms_sender_id')) {
            $this->migrator->add('integrations.kudisms_sender_id', '');
        }
    }
};
