<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('integrations.openai_api_key', '');
        $this->migrator->add('integrations.openai_model', 'gpt-4o-mini');
    }
};
