---
name: filament-forms-v4
description: >-
  Designs and implements Filament v4 Resource Forms. Activates when creating or updating
  Filament Resource forms; working with form components like TextInput, Select, DatePicker,
  FileUpload, Repeater; handling relationships via relationship(); organizing layout with
  Section/Grid/Fieldset/Tabs; validating and saving form state; or writing tests for
  Filament forms.
---
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Filament v4 Forms

## When to Apply

Activate this skill when:
- Creating or modifying Filament Resource forms (Create/Edit/Manage Related Records)
- Adding or refining form components, validation, and layout
- Handling Eloquent relationships in form inputs
- Implementing file uploads, repeaters, tabs/wizard flows
- Writing tests for Filament forms and form-driven pages

## Documentation

Use `search-docs` for Filament v4 form component references, patterns, and version-specific behavior.

## Basic Usage

### Creating Resources & Pages

- Create a Resource: `{{ $assist->artisanCommand('make:filament-resource [User] --no-interaction') }}`
- Create a Page: `{{ $assist->artisanCommand('make:filament-page [Settings] --no-interaction') }}`
- Create a Widget: `{{ $assist->artisanCommand('make:filament-widget [Stats] --no-interaction') }}`
- Multiple panels: add `--panel=[app]` when targeting a specific panel

### Fundamental Concepts

- Define forms using `Filament\Forms\Form` and components under `Filament\Forms\Components`.
- In Resources, implement `public static function form(Form $form): Form` and return a `->schema([...])`.
- Prefer Eloquent-bound inputs via `->relationship()` rather than manual option arrays when applicable.
- Organize forms with layout components from `Filament\Schemas\Components` (`Section`, `Grid`, `Fieldset`, `Tabs`, `Wizard`).

### File Locations (v4)

- Resource folder: `app/Filament/Resources/{PluralModel}/`
    - Main resource: `{Singular}Resource.php` (e.g., `Customers/CustomerResource.php`)
    - Pages: `Customers/Pages/` → `List{Plural}.php`, `Create{Singular}.php`, `Edit{Singular}.php`
    - Schemas: `Customers/Schemas/` → `{Singular}Form.php`
    - Tables: `Customers/Tables/` → `{Plural}Table.php`
- Widgets: `app/Filament/Widgets/`
- Panels (when used): typically configured under `app/Providers/Filament/`

## Filament v4 Specifics

- Layout components moved to `Filament\Schemas\Components` (e.g., `Section`, `Grid`, `Fieldset`, `Tabs`, `Wizard`).
- `FileUpload` default visibility is `private`; set explicit visibility as needed.
- `Repeater` is available for complex nested data and remains under `Filament\Forms\Components`.
- Action classes extend `Filament\Actions\Action` (when customizing page actions for form pages).

## Best Practices

### Resource Form Example

@boostsnippet("Resource Form Example (v4)", "php")
use Filament\Forms; // optional alias
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make('Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->unique(ignoreRecord: true)
                                ->required(),

                            Select::make('role_id')
                                ->label('Role')
                                ->relationship('role', 'name')
                                ->preload()
                                ->searchable()
                                ->required(),

                            DatePicker::make('joined_at')
                                ->label('Joined')
                                ->native(false),
                        ]),
                ]),
        ]);
}
@endboostsnippet

### Relationships

- Use `->relationship('author', 'name')` for Eloquent-bound selects; combine `->preload()` and `->searchable()` for UX.
- Constrain options with `modifyQueryUsing`: `->relationship('author', 'name', modifyQueryUsing: fn ($q) => $q->where('active', true))`.

### Common Components

@boostsnippet("Common Components (v4)", "php")
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Fieldset;

TextInput::make('title')->required()->maxLength(120);

Textarea::make('notes')->rows(4)->columnSpanFull();

Toggle::make('is_active')->label('Active')->inline(false);

CheckboxList::make('tags')
    ->options(Tag::query()->pluck('name', 'id'))
    ->columns(2);

Radio::make('status')->options([
    'draft' => 'Draft',
    'published' => 'Published',
]);

FileUpload::make('attachment')
    ->directory('attachments')
    ->disk('public')
    ->visibility('private'); // v4 default is private; set explicitly

Repeater::make('items')
    ->schema([
        Fieldset::make('Line')
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('qty')->numeric()->required(),
            ]),
    ])
    ->defaultItems(1)
    ->minItems(0)
    ->collapsible();
@endboostsnippet

### Reactive & Dependent Fields

- Use `->reactive()` to recalculate on state changes.
- Use `->afterStateUpdated(fn (\Filament\Forms\Set $set, $state) => ...)` to compute dependent values.

### Validation & Rules

- Prefer component-level constraints: `->required()`, `->minLength(3)`, `->maxLength(255)`, `->email()`.
- Add unique rules with record ignoring on Resources: `->unique(ignoreRecord: true)`.
- Keep authorization via policies; treat form actions like HTTP requests.

### Layout Components

- `Section::make('Heading')->schema([...])` for logical grouping.
- `Grid::make(1|2|3)` to control responsive columns.
- `Fieldset::make('Group')` for labeled sub-areas.
- Use `Tabs` / `Wizard` for long forms (both under `Filament\Schemas\Components`).

## Testing

@boostsnippet("Create Resource via Form Test (v4)", "php")
use function Pest\Laravel\actingAs;
use Filament\Facades\Filament;
use App\Filament\Resources\UserResource\Pages\CreateUser;

it('creates a user via Filament form', function () {
    actingAs(User::factory()->create());

    Filament::setCurrentPanel('app');

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
            'role_id' => Role::factory()->create()->id,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'email' => 'howdy@example.com',
    ]);
});
@endboostsnippet

## Common Pitfalls

- Import layout components from `Filament\Schemas\Components`, not `Filament\Forms\Components`
- Not configuring `FileUpload` disk/visibility explicitly (visibility is private by default)
- Manual option arrays where `->relationship()` is preferred
- Missing `unique(..., ignoreRecord: true)` on Resource edit forms
