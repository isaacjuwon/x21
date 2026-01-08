<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public ?string $site_logo;

    public ?string $site_dark_logo;

    public ?string $site_favicon;

    public ?string $site_dark_favicon;

    public string $site_description;

    public string $contact_email;

    public string $support_email;

    public bool $maintenance_mode;

    public bool $registration_enabled;

    public string $currency;

    public string $timezone;

    public static function group(): string
    {
        return 'general';
    }
}
