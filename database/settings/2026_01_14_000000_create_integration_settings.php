<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Paystack
        $this->migrator->add('integration.paystack_public_key', config('services.paystack.public_key'));
        $this->migrator->add('integration.paystack_secret_key', config('services.paystack.secret_key'));
        $this->migrator->add('integration.paystack_url', config('services.paystack.url', 'https://api.paystack.co'));

        // Epins
        $this->migrator->add('integration.epins_api_key', config('services.epins.api_key'));
        $this->migrator->add('integration.epins_url', config('services.epins.url', 'https://api.epins.com.ng/core'));
        $this->migrator->add('integration.epins_sandbox_url', config('services.epins.sandbox_url', 'https://sandbox.epins.com.ng/core'));

        // Dojah
        $this->migrator->add('integration.dojah_api_key', config('services.dojah.api_key'));
        $this->migrator->add('integration.dojah_app_id', config('services.dojah.app_id'));
        $this->migrator->add('integration.dojah_base_url', config('services.dojah.base_url', 'https://api.dojah.io'));
    }
};
