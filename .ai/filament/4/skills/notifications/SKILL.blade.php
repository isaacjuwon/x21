---
name: filament-notifications-v4
description: >-
  Designs and implements Filament v4 Notifications. Activates when sending toast notifications
  in actions, pages, tables, or schemas; configuring database notifications; rendering the
  notifications component; and writing tests for notifications.
---
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Filament v4 Notifications

## When to Apply

Activate this skill when:
- Sending user feedback from actions, pages, tables, or schemas
- Rendering the notifications Livewire component in app layout
- Configuring and testing database notifications in panels
- Writing tests to assert notifications were sent

## Documentation

Use `search-docs` for Filament v4 notifications references (rendering, sending, database notifications, testing).

## Rendering Notifications

Ensure the notifications Livewire component is present in your app layout to display toast notifications:

@boostsnippet("Render Notifications Component", "blade")
<div>
	@livewire('notifications')
</div>
@endboostsnippet

## Sending Toast Notifications

@boostsnippet("Sending Toast Notifications (v4)", "php")
use Filament\Notifications\Notification;

// Basic success notification
Notification::make()
	->success()
	->title('Saved successfully')
	->send();

// Danger notification with body
Notification::make()
	->danger()
	->title('Unable to save')
	->body('Please check validation errors and try again.')
	->send();

// Info notification with duration and persistent close button
Notification::make()
	->info()
	->title('Export started')
	->body('You will be notified when the export is complete.')
	->duration(5000)
	->persistent()
	->send();
@endboostsnippet

### Using Notifications in Actions

@boostsnippet("Notifications in Actions (v4)", "php")
use Filament\Actions\Action;
use Filament\Notifications\Notification;

Action::make('send_email')
	->action(function (User $record, array $data) {
		Mail::to($record)->send(new CustomEmail($data));

		Notification::make()
			->success()
			->title('Email sent')
			->send();
	});
@endboostsnippet

## Database Notifications

### Enabling Database Notifications in a Panel

@boostsnippet("Enable Database Notifications (v4)", "php")
// In your Panel provider or configuration
use Filament\Panels\Panel;

Panel::make()
	// ... other configuration
	->databaseNotifications();
@endboostsnippet

### Migrations

Publish and run notifications migrations:

@boostsnippet("Install Notifications Migrations", "bash")
php artisan make:notifications-table
php artisan migrate
@endboostsnippet

### Sending Database Notifications

@boostsnippet("Send Database Notification", "php")
use Filament\Notifications\Notification;

Notification::make()
	->title('Report ready')
	->body('Click to download the report.')
	->actions([
		// Optional action button in the toast
	])
	->sendToDatabase(auth()->user());
@endboostsnippet

## v4 Specifics

- Render the `@livewire('notifications')` component in your layout to display toasts.
- Database notifications require enabling in panel configuration and migrations.
- Icons use the `Filament\Support\Icons\Heroicon` enum across actions; notifications do not require icon enum unless customizing.
- Tailwind v4 changes may affect custom themes; ensure your theme includes `@source` entries so notifications styles are present.

## Testing Notifications

@boostsnippet("Testing Notifications (Livewire helper)", "php")
use function Pest\Livewire\livewire;

it('sends a notification', function () {
	livewire(CreatePost::class)
		->assertNotified();
});
@endboostsnippet

@boostsnippet("Testing Notifications (Static helper)", "php")
use Filament\Notifications\Notification;

it('sends a notification', function () {
	Notification::assertNotified();
});
@endboostsnippet

@boostsnippet("Testing Notification Title", "php")
use function Pest\Livewire\livewire;

it('sends a danger notification with title', function () {
	livewire(CreatePost::class)
		->assertNotified('Unable to create post');
});
@endboostsnippet

@boostsnippet("Testing Specific Notification Object", "php")
use Filament\Notifications\Notification;
use function Pest\Livewire\livewire;

it('sends the exact notification', function () {
	livewire(CreatePost::class)
		->assertNotified(
			Notification::make()
				->danger()
				->title('Unable to create post')
				->body('Something went wrong.'),
		);
});
@endboostsnippet

@boostsnippet("Assert Not Notified", "php")
use Filament\Notifications\Notification;
use function Pest\Livewire\livewire;

it('does not send a notification', function () {
	livewire(CreatePost::class)
		->assertNotNotified()
		->assertNotNotified('Unable to create post')
		->assertNotNotified(
			Notification::make()
				->danger()
				->title('Unable to create post')
				->body('Something went wrong.'),
		);
});
@endboostsnippet

## Best Practices

- Use notifications to provide clear, immediate feedback to the user
- Prefer concise titles with optional body for details
- Use `success()`, `info()`, `warning()`, `danger()` variants consistently
- Keep destructive flows paired with a confirmation action and follow-up notification
- For long operations, notify start and completion; consider database notifications for persistence

## Common Pitfalls

- Forgetting to render the `@livewire('notifications')` component in the layout
- Not enabling database notifications in the panel before using `sendToDatabase()`
- Neglecting migrations for notifications table
- Overusing notifications for non-critical information (noise)
- Missing tests for critical notifications

## Quick Reference

@boostsnippet("Notifications Quick Imports", "php")
use Filament\Notifications\Notification;
use Filament\Actions\Action;
@endboostsnippet
