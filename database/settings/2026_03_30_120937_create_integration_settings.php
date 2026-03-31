<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('integrations.paystack_url', 'https://api.paystack.co');
        $this->migrator->add('integrations.paystack_public_key', '');
        $this->migrator->add('integrations.paystack_secret_key', '');

        $this->migrator->add('integrations.dojah_base_url', 'https://api.dojah.io');
        $this->migrator->add('integrations.dojah_app_id', '');
        $this->migrator->add('integrations.dojah_api_key', '');

        $this->migrator->add('integrations.epins_url', 'https://api.epins.com.ng/v1');
        $this->migrator->add('integrations.epins_api_key', '');
    }
};
