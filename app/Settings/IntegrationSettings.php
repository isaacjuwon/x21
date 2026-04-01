<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class IntegrationSettings extends Settings
{
    public ?string $paystack_url;

    public ?string $paystack_public_key;

    public ?string $paystack_secret_key;

    public ?string $dojah_base_url;

    public ?string $dojah_app_id;

    public ?string $dojah_api_key;

    public ?string $epins_url;

    public ?string $epins_api_key;

    public ?string $openai_api_key;

    public ?string $openai_model;

    public static function group(): string
    {
        return 'integrations';
    }
}
