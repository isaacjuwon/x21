<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LayoutSettings extends Settings
{
    public string $primary_color;
    public bool $sidebar_collapsible;
    public string $font_family;

    public string $homepage_features_title;
    public string $homepage_features_description;
    public array $homepage_features_items;

    public ?string $banner;
    public ?string $about;
    public ?string $address;
    public ?string $facebook;
    public ?string $twitter;
    public ?string $instagram;
    public ?string $email;
    public string $homepage_title;
    public string $homepage_description;

    public static function group(): string
    {
        return 'layout';
    }
}
