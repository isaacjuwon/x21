---
name: filament-infolists-v4
description: >-
  Designs and implements Filament v4 Infolists. Activates when creating or updating
  read-only data displays in resources and pages; selecting and formatting entries
  like TextEntry, ImageEntry, IconEntry; organizing layout with Section/Grid/Fieldset;
  adding actions in schemas; handling relationships and computed state; and writing
  tests for infolists and schema state.
---
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Filament v4 Infolists

## When to Apply

Activate this skill when:
- Building or modifying resource View pages (read-only displays)
- Adding or refining infolist entries and formatting
- Organizing infolist layout with Sections, Grids, and Fieldsets
- Displaying related model data and computed state
- Adding actions inside schemas (entries area) and testing them

## Documentation

Use `search-docs` for Filament v4 infolists references, entry APIs, layout components, and testing schemas.

## Basics

- Define infolists using `Filament\Infolists\Infolist` and components under `Filament\Infolists\Components`.
- Layout components for Infolists (like `Section`, `Grid`, `Fieldset`) live under `Filament\Schemas\Components` in v4.
- In Resources, implement `public static function infolist(Infolist $infolist): Infolist` and return a `->schema([...])`.
- Prefer computed state via `->state()` or `->formatStateUsing()` to present derived values.

## File Locations (v4)

- Resource folder: `app/Filament/Resources/{PluralModel}/`
  - Main resource: `{Singular}Resource.php`
  - Pages: `{Plural}/Pages/View{Singular}.php` for View pages
  - Schemas: `{Plural}/Schemas/{Singular}Infolist.php` (optional organization)

## Resource Infolist Example

@boostsnippet("Resource Infolist Example (v4)", "php")
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

public static function infolist(Infolist $infolist): Infolist
{
	return $infolist
		->schema([
			Section::make('Profile')
				->schema([
					Grid::make(2)
						->schema([
							ImageEntry::make('avatar')
								->label('Avatar')
								->circular(),

							TextEntry::make('name')
								->label('Full Name')
								->formatStateUsing(fn ($state) => trim($state)),

							TextEntry::make('email')
								->label('Email'),

							IconEntry::make('is_active')
								->label('Active')
								->boolean(),
						]),
				]),

			Section::make('Meta')
				->schema([
					Grid::make(2)
						->schema([
							TextEntry::make('created_at')
								->label('Created')
								->dateTime(),

							TextEntry::make('updated_at')
								->label('Updated')
								->since(),
						]),
				]),
		]);
}
@endboostsnippet

## Common Entries

@boostsnippet("Common Infolist Entries (v4)", "php")
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ViewEntry;

// Text entry with custom formatting
TextEntry::make('full_name')
	->label('Name')
	->state(fn (User $record): string => "{$record->first_name} {$record->last_name}")
	->formatStateUsing(fn (string $state) => strtoupper($state));

// Image entry (v4: default visibility can be private on non-local disks)
ImageEntry::make('avatar')
	->label('Avatar')
	->circular();

// Boolean icon entry
IconEntry::make('is_active')
	->label('Active')
	->boolean();

// Custom Blade view entry
ViewEntry::make('status_badge')
	->view('filament.infolists.entries.status-badge');
@endboostsnippet

### Relationship Data

@boostsnippet("Relationship Entries (v4)", "php")
use Filament\Infolists\Components\TextEntry;

TextEntry::make('author.name')
	->label('Author');
@endboostsnippet

## Layout (v4 changes)

@boostsnippet("Layout Components for Infolists (v4)", "php")
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;

Section::make('Details')
	->schema([
		Grid::make(2)
			->schema([
				Fieldset::make('Contact')
					->schema([
						TextEntry::make('email'),
						TextEntry::make('phone'),
					]),
			]),
	]);

// In v4, Grid/Section/Fieldset do not span all columns by default.
// Use columnSpanFull() when you need full-width:
Section::make('Notes')
	->columnSpanFull();
@endboostsnippet

## Computed & Cross-Component State

@boostsnippet("Computed and Cross-Component State (v4)", "php")
use Filament\Schemas\Components\Utilities\Get;

// Compute state using ->state()
TextEntry::make('slug')
	->state(fn (User $record) => Str::slug($record->name));

// Access another entry's state
TextEntry::make('domain')
	->state(function (Get $get) {
		$email = $get('email');
		return str($email)->after('@');
	});
@endboostsnippet

## Actions in Infolists (Schemas)

@boostsnippet("Schema Actions in Infolists (v4)", "php")
use Filament\Actions\Action;
use Filament\Notifications\Notification;

// Actions can be added within schema components context (e.g., below content areas).
// They behave like standard actions and can open modals, run logic, and show notifications.
Action::make('send_welcome')
	->label('Send Welcome Email')
	->requiresConfirmation()
	->action(function (User $record) {
		Mail::to($record)->send(new WelcomeMail());
		Notification::make()->success()->title('Email sent')->send();
	});
@endboostsnippet

## Testing Infolists & Schemas

@boostsnippet("Testing View Page Infolist (v4)", "php")
use App\Filament\Resources\UserResource\Pages\ViewUser;
use function Pest\Livewire\livewire;

it('loads and shows infolist state', function () {
	$user = User::factory()->create();

	livewire(ViewUser::class, ['record' => $user->id])
		->assertOk()
		->assertSchemaStateSet([
			'name' => $user->name,
			'email' => $user->email,
		]);
});
@endboostsnippet

@boostsnippet("Testing Actions in Schemas (v4)", "php")
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

$user = User::factory()->create();

livewire(ViewUser::class, ['record' => $user->id])
	->callAction(TestAction::make('send_welcome')->schemaComponent('email'))
	->assertNotified();
@endboostsnippet

## Filament v4 Specifics

- Layout components (`Grid`, `Section`, `Fieldset`) moved to `Filament\Schemas\Components` and no longer span full width by default.
- `ImageEntry` default visibility is `private` for non-local disks (e.g., `s3`). Configure visibility if needed.
- Actions can be used within schemas; test with `TestAction::make(...)->schemaComponent('componentName')`.
- Use utility injection (`Get $get`) to access other entry states.

## Best Practices

- Keep infolists read-only; use actions for interactive operations
- Use layout components to organize content clearly
- Favor computed state with `->state()` / `->formatStateUsing()` for derived values
- Display relationship data via dot notation (e.g., `author.name`)
- Configure image visibility explicitly when using non-local disks
- Write schema tests using `assertSchemaStateSet()` for critical entries

## Common Pitfalls

- Importing layout components from `Filament\Forms\Components` instead of `Filament\Schemas\Components`
- Forgetting to set `columnSpanFull()` when you need full-width sections
- Not accounting for private default visibility on `ImageEntry` for `s3`/remote disks
- Overusing custom views when built-in entries suffice
- Not testing schema state or actions

## Quick Reference

@boostsnippet("Common Infolist Imports (v4)", "php")
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{TextEntry, ImageEntry, IconEntry, ViewEntry};
use Filament\Schemas\Components\{Section, Grid, Fieldset};
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\{Action};
use Filament\Notifications\Notification;
@endboostsnippet
