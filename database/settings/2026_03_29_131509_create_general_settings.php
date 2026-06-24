<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'RDX Platform');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.site_dark_logo', null);
        $this->migrator->add('general.site_favicon', null);
        $this->migrator->add('general.site_dark_favicon', null);
        $this->migrator->add('general.site_description', 'Cooperative Management System');
        $this->migrator->add('general.contact_email', 'contact@rdx.com');
        $this->migrator->add('general.support_email', 'support@rdx.com');
        $this->migrator->add('general.maintenance_mode', false);
        $this->migrator->add('general.registration_enabled', true);
        $this->migrator->add('general.currency', 'NGN');
        $this->migrator->add('general.timezone', 'Africa/Lagos');
    }
};
