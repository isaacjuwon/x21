<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class IntegrationSettings extends Settings
{
    // Paystack
    public ?string $paystack_public_key;
    public ?string $paystack_secret_key;
    public ?string $paystack_url;

    // Epins
    public ?string $epins_api_key;
    public ?string $epins_url;
    public ?string $epins_sandbox_url;

    // Dojah
    public ?string $dojah_api_key;
    public ?string $dojah_app_id;
    public ?string $dojah_base_url;

    public static function group(): string
    {
        return 'integration';
    }
}
