<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('integrations.openai_api_key')) {
            $this->migrator->add('integrations.openai_api_key', '');
        }

        if (! $this->migrator->exists('integrations.openai_model')) {
            $this->migrator->add('integrations.openai_model', 'gpt-4o-mini');
        }
    }
};
