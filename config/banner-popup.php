<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    | The URI prefix for the package's internal HTTP routes.
    | Example: 'banner-popup' → GET /banner-popup/active
    |
    */
    'route_prefix' => 'banner-popup',

    /*
    |--------------------------------------------------------------------------
    | Route middleware
    |--------------------------------------------------------------------------
    | Applied to all package routes. 'web' is required for session/cookie
    | support used by show_frequency tracking.
    |
    */
    'route_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Table name
    |--------------------------------------------------------------------------
    | Override if your project uses a custom database prefix or naming convention.
    |
    */
    'table_name' => 'banner_popups',

    /*
    |--------------------------------------------------------------------------
    | Accepted MIME types
    |--------------------------------------------------------------------------
    | File types allowed when uploading banner images through Filament.
    |
    */
    'accepted_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Show frequency cookie prefix
    |--------------------------------------------------------------------------
    | Cookies are named: {prefix}{banner_id}
    | Example: bp_12
    |
    */
    'cookie_prefix' => 'bp_',

    /*
    |--------------------------------------------------------------------------
    | Route filter: prefixes to exclude from target_pages selector
    |--------------------------------------------------------------------------
    | Route names starting with any of these strings will not appear in the
    | Filament "target pages" multi-select.
    |
    */
    'excluded_route_prefixes' => [
        'filament',
        'livewire',
        'ignition',
        'impersonate',
        'log-viewer',
        'whatsapp-widget',
        'sanctum',
        'sitemap',
        'robots',
        'debugbar',
    ],

];
