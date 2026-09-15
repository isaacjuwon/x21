# inudev/banner-popup

Configurable popup banner module for Laravel applications with Filament v3 admin panel support.

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | ^11.0 or ^12.0 |
| Filament | ^3.0 |
| spatie/laravel-medialibrary | ^11.0 |

## Installation

```bash
composer require inudev/banner-popup
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag="banner-popup-migrations"
php artisan migrate
```

Publish the frontend assets (JS + CSS):

```bash
php artisan vendor:publish --tag="banner-popup-assets"
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag="banner-popup-config"
```

## Filament Panel setup

Register the plugin in your Filament panel provider:

```php
use Inudev\BannerPopup\BannerPopupPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            BannerPopupPlugin::make(),
        ]);
}
```

## Frontend setup

Add the Blade component just before `</body>` in your main layout:

```blade
{{-- resources/views/layouts/app.blade.php --}}
    ...
    <x-banner-popup />
</body>
```

That's it. The component automatically injects the CSS link, the JS config object (with the current route name), and the deferred JS bundle.

## How it works

1. On each page load, `banner-popup.js` calls `GET /banner-popup/active?page={route.name}`.
2. The server returns all active banners whose `target_pages` includes the current route (or banners with no page restriction).
3. The JS checks a cookie (`bp_{id}`) against `show_frequency` before triggering each banner.
4. Each banner is triggered according to its `trigger` type:
   - `page_load` → shown immediately
   - `delay` → shown after `trigger_delay` seconds
   - `exit_intent` → shown when the cursor leaves the top of the viewport
5. On dismissal, the cookie is updated and a silent `POST /banner-popup/{id}/seen` is fired.

## Configuration

```php
// config/banner-popup.php

return [
    'route_prefix'            => 'banner-popup',
    'route_middleware'        => ['web'],
    'table_name'              => 'banner_popups',
    'accepted_mime_types'     => ['image/jpeg', 'image/png', 'image/webp'],
    'cookie_prefix'           => 'bp_',
    'excluded_route_prefixes' => ['filament', 'livewire', ...],
];
```

## Customising the popup styles

Override CSS variables in your own stylesheet after publishing the assets:

```css
:root {
    --bp-box-max-width:  800px;
    --bp-overlay-bg:     rgba(0, 0, 0, 0.75);
    --bp-box-radius:     12px;
    --bp-close-bg:       #e53e3e;
}
```

## Customising the Blade view

```bash
php artisan vendor:publish --tag="banner-popup-views"
```

The view will be placed at `resources/views/vendor/banner-popup/components/banner-popup.blade.php`.

## Roadmap (v2)

- [ ] Impression analytics table
- [ ] User/role-based segmentation
- [ ] HTML/rich-text banner type
- [ ] Video banner type
- [ ] A/B testing support

## Testing

The package ships with a full Pest test suite powered by Orchestra Testbench (SQLite in-memory, no external Laravel project needed).

```bash
# Install dev dependencies
composer install

# Run all tests
composer test

# Run only unit tests
./vendor/bin/pest tests/Unit

# Run only feature tests
./vendor/bin/pest tests/Feature

# Run with coverage (requires Xdebug or PCOV)
composer test:cover
```

### Test structure

```
tests/
├── Pest.php                          # Global config + banner() helper
├── TestCase.php                      # Boots Testbench + migrations
├── Unit/
│   ├── BannerPopupModelTest.php      # scopeActive, isCurrentlyActive, casts, SoftDeletes
│   └── BannerPopupManagerTest.php    # active() filtering, find()
└── Feature/
    ├── BannerPopupControllerTest.php # HTTP endpoints: /active and /seen
    └── ServiceProviderTest.php       # Container bindings, config, routes, schema
```
