---
name: filament-core-v4
description: >-
  Core guidelines and concepts for Filament v4 development. Covers fundamental architecture,
  resources, panels, navigation, artisan commands, file organization, version 4 breaking changes,
  testing strategies, and best practices. Essential reference for all Filament v4 development.
---
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Filament v4 - Core Guidelines

## Overview

Filament is a Server-Driven UI (SDUI) framework for Laravel that allows developers to define user interfaces in PHP using structured configuration objects. Built on top of Livewire, Alpine.js, and Tailwind CSS, Filament provides a powerful and elegant way to build admin panels and CRUD interfaces.

## When to Apply

Use these core guidelines when:
- Starting a new Filament project
- Creating resources, pages, or widgets
- Understanding Filament's architecture and conventions
- Troubleshooting Filament-specific issues
- Working with panels and navigation
- Following Filament v4 best practices

## Documentation

Use `search-docs` for Filament v4 documentation, examples, and version-specific behavior. Always search before implementing features.

## Filament's Core Features

### Resources

Static classes that build CRUD interfaces for Eloquent models. Resources define how administrators interact with data using tables and forms.

**Location:** `app/Filament/Resources/`

@boostsnippet("Resource Structure", "text")
app/Filament/Resources/
└── Customers/
    ├── CustomerResource.php       # Main resource class
    ├── Pages/                      # Livewire page components
    │   ├── ListCustomers.php
    │   ├── CreateCustomer.php
    │   └── EditCustomer.php
    ├── Schemas/                    # Form/Infolist definitions
    │   └── CustomerForm.php
    └── Tables/                     # Table definitions
        └── CustomersTable.php
@endboostsnippet

### Actions

Handle user interactions with buttons or links. Actions encapsulate UI, modals, and logic execution. Used for one-time operations like deleting records, sending emails, or updating data.

**All action classes extend:** `Filament\Actions\Action`

### Forms

Dynamic forms rendered within resources, action modals, table filters, and more. Forms use components from `Filament\Forms\Components`.

### Tables

Interactive data tables with filtering, sorting, pagination, searching, and bulk actions. Tables use columns from `Filament\Tables\Columns`.

### Infolists

Read-only lists of data for displaying information without editing capability.

### Schemas

Components that define the structure and behavior of UI elements (forms, tables, lists). Layout components are in `Filament\Schemas\Components`.

### Panels

Top-level containers that include pages, resources, forms, tables, notifications, actions, infolists, and widgets.

### Widgets

Small components for dashboards, displaying data in charts, tables, or stats.

### Notifications

Flash notifications displayed to users within the application.

## Artisan Commands

Always use Filament-specific Artisan commands. Use `list-artisan-commands` tool to verify parameters.

### Creating Resources

@boostsnippet("Create Resource Commands", "bash")
# Basic resource
{{ $assist->artisanCommand('make:filament-resource [Customer] --no-interaction') }}

# Simple (modal) resource
{{ $assist->artisanCommand('make:filament-resource [Customer] --simple --no-interaction') }}

# Auto-generate from database
{{ $assist->artisanCommand('make:filament-resource [Customer] --generate --no-interaction') }}

# With soft deletes
{{ $assist->artisanCommand('make:filament-resource [Customer] --soft-deletes --no-interaction') }}

# With view page
{{ $assist->artisanCommand('make:filament-resource [Customer] --view --no-interaction') }}

# Custom model namespace
{{ $assist->artisanCommand('make:filament-resource [Customer] --model-namespace=Custom\\\\Path\\\\Models --no-interaction') }}

# Generate model, migration, and factory
{{ $assist->artisanCommand('make:filament-resource [Customer] --model --migration --factory --no-interaction') }}

# For specific panel
{{ $assist->artisanCommand('make:filament-resource [Customer] --panel=app --no-interaction') }}
@endboostsnippet

### Creating Pages

@boostsnippet("Create Page Commands", "bash")
# Custom page
{{ $assist->artisanCommand('make:filament-page [Settings] --no-interaction') }}

# Resource page
{{ $assist->artisanCommand('make:filament-page [ManageSettings] --resource=SettingResource --type=ManageRecords --no-interaction') }}

# For specific panel
{{ $assist->artisanCommand('make:filament-page [Settings] --panel=app --no-interaction') }}
@endboostsnippet

### Creating Widgets

@boostsnippet("Create Widget Commands", "bash")
# Basic widget
{{ $assist->artisanCommand('make:filament-widget [StatsOverview] --no-interaction') }}

# Chart widget
{{ $assist->artisanCommand('make:filament-widget [SalesChart] --chart --no-interaction') }}

# Stats widget
{{ $assist->artisanCommand('make:filament-widget [StatsOverview] --stats --no-interaction') }}

# For specific panel
{{ $assist->artisanCommand('make:filament-widget [StatsOverview] --panel=app --no-interaction') }}

# Resource widget
{{ $assist->artisanCommand('make:filament-widget [CustomerStats] --resource=CustomerResource --no-interaction') }}
@endboostsnippet

### Creating Other Components

@boostsnippet("Additional Commands", "bash")
# Relation manager
{{ $assist->artisanCommand('make:filament-relation-manager [CustomerResource] [orders] [OrdersRelationManager] --no-interaction') }}

# User
{{ $assist->artisanCommand('make:filament-user --no-interaction') }}

# Panel
{{ $assist->artisanCommand('make:filament-panel [app] --no-interaction') }}
@endboostsnippet

## File Organization (v4 Structure)

### Resource Organization

@boostsnippet("Resource File Structure", "php")
namespace App\Filament\Resources\Customers;

// Main Resource Class
class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    
    // References separate form file
    public static function form(Form $form): Form
    {
        return CustomerForm::configure($form);
    }
    
    // References separate table file
    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }
}

// Separate Form File (Schemas/CustomerForm.php)
class CustomerForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([...]);
    }
}

// Separate Table File (Tables/CustomersTable.php)
class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([...])->filters([...]);
    }
}
@endboostsnippet

### Custom Component Organization

@boostsnippet("Component Class Structure", "text")
app/Filament/Resources/{PluralModel}/
├── Schemas/
│   └── Components/          # Custom form/infolist components
│       └── CustomField.php
├── Tables/
│   ├── Columns/            # Custom table columns
│   │   └── StatusColumn.php
│   ├── Filters/            # Custom table filters
│   │   └── DateRangeFilter.php
│   └── Actions/            # Custom actions (if needed)
└── Pages/                  # Page components
    └── CustomPage.php
@endboostsnippet

## Filament v4 Breaking Changes

### Critical Changes

1. **Deferred Filters (Default)**
   - Filters now require users to click "Apply" button
   - Use `->deferFilters(false)` to restore immediate filtering

2. **Action Method Names**
   - `->actions([])` → `->recordActions([])` (row actions)
   - `->bulkActions([])` → `->toolbarActions([])` (bulk actions)
   - New: `->headerActions([])` for header actions

3. **Action Classes Unified**
   - All actions extend `Filament\Actions\Action`
   - No more `Filament\Tables\Actions` namespace

4. **Layout Components Moved**
   - `Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard` moved to `Filament\Schemas\Components`
   - No longer span all columns by default

5. **File Visibility**
   - `FileUpload` default visibility is now `private`
   - Explicitly set visibility when needed

6. **Icons**
   - Use `Filament\Support\Icons\Heroicon` enum instead of strings
   - Example: `Heroicon::Pencil` instead of `'heroicon-o-pencil'`

7. **Pagination**
   - `'all'` option not available by default
   - Must explicitly add: `->paginated([10, 25, 50, 'all'])`

8. **New Components**
   - `Repeater` component added to Forms
   - Enhanced relationship management

### Import Changes

@boostsnippet("Correct v4 Imports", "php")
// Actions (v4)
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

// Layout Components (v4)
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Wizard;

// Icons (v4)
use Filament\Support\Icons\Heroicon;

// Forms (unchanged)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
// ...

// Tables (unchanged)
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
// ...
@endboostsnippet

## Testing Guidelines

### Setup

- Always authenticate users before testing Filament
- Use `livewire()` helper from Pest
- Set current panel when using multiple panels

@boostsnippet("Test Setup", "php")
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;
use Filament\Facades\Filament;

beforeEach(function () {
    actingAs(User::factory()->create());
    
    // For multiple panels
    Filament::setCurrentPanel('app');
});
@endboostsnippet

### Testing Resources

@boostsnippet("Resource Testing Patterns", "php")
// Test list page renders
it('can render list page', function () {
    livewire(ListCustomers::class)
        ->assertSuccessful();
});

// Test create functionality
it('can create customer', function () {
    livewire(CreateCustomer::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();
    
    assertDatabaseHas(Customer::class, [
        'email' => 'john@example.com',
    ]);
});

// Test edit functionality
it('can update customer', function () {
    $customer = Customer::factory()->create();
    
    livewire(EditCustomer::class, ['record' => $customer->id])
        ->fillForm([
            'name' => 'Jane Doe',
        ])
        ->call('save')
        ->assertNotified();
    
    expect($customer->refresh()->name)->toBe('Jane Doe');
});
@endboostsnippet

### Testing Actions

@boostsnippet("Action Testing Patterns", "php")
use Filament\Actions\Testing\TestAction;

// Test page action
it('can call send action', function () {
    $invoice = Invoice::factory()->create();
    
    livewire(EditInvoice::class, ['invoice' => $invoice])
        ->callAction('send');
    
    expect($invoice->refresh()->isSent())->toBeTrue();
});

// Test table action
it('can delete customer', function () {
    $customer = Customer::factory()->create();
    
    livewire(ListCustomers::class)
        ->callAction(TestAction::make('delete')->table($customer))
        ->assertNotified();
    
    assertDatabaseMissing('customers', ['id' => $customer->id]);
});

// Test bulk action
it('can bulk delete customers', function () {
    $customers = Customer::factory()->count(3)->create();
    
    livewire(ListCustomers::class)
        ->selectTableRecords($customers)
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertNotified();
});
@endboostsnippet

## Relationships

Use the `relationship()` method on form components when you need options for selects, checkboxes, or repeaters.

@boostsnippet("Relationship Examples", "php")
// Select with relationship
Select::make('user_id')
    ->label('Author')
    ->relationship('author', 'name')
    ->searchable()
    ->preload()
    ->required(),

// Relationship with query modification
Select::make('category_id')
    ->relationship('category', 'name', modifyQueryUsing: fn ($query) => 
        $query->where('active', true)
    )
    ->required(),

// Multiple select with relationship
Select::make('tags')
    ->relationship('tags', 'name')
    ->multiple()
    ->preload()
    ->searchable(),

// Checkbox list with relationship
CheckboxList::make('roles')
    ->relationship('roles', 'name')
    ->columns(2),
@endboostsnippet

## Navigation & Panels

### Resource Navigation

@boostsnippet("Navigation Configuration", "php")
class CustomerResource extends Resource
{
    // Navigation label
    protected static ?string $navigationLabel = 'Clients';
    
    // Navigation icon
    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    // Navigation group
    protected static ?string $navigationGroup = 'Shop';
    
    // Navigation sort order
    protected static ?int $navigationSort = 2;
    
    // Hide from navigation
    protected static bool $shouldRegisterNavigation = false;
    
    // Navigation badge
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    // Badge color
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
@endboostsnippet

### Custom Pages Navigation

@boostsnippet("Custom Page Navigation", "php")
class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 99;
    
    protected static string $view = 'filament.pages.settings';
}
@endboostsnippet

### Page Actions

@boostsnippet("Page Header Actions", "php")
class EditCustomer extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('send_email')
                ->icon(Heroicon::Envelope)
                ->action(function () {
                    // Send email logic
                    
                    Notification::make()
                        ->success()
                        ->title('Email sent')
                        ->send();
                }),
        ];
    }
}
@endboostsnippet

## Best Practices

### Resource Development

1. **Use separate files** for forms and tables to keep resources organized
2. **Search documentation** before implementing features
3. **Use relationship() method** for related data in forms
4. **Eager load relationships** in table queries to avoid N+1
5. **Test critical functionality** with Pest tests
6. **Follow naming conventions** for consistency

### Code Organization

@boostsnippet("Organization Best Practices", "php")
// Good: Separate form file
class CustomerResource extends Resource
{
    public static function form(Form $form): Form
    {
        return CustomerForm::configure($form);
    }
}

// Good: Eager loading in table
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn ($query) => $query->with(['user', 'category']))
        ->columns([...]);
}

// Good: Using relationship method
Select::make('category_id')
    ->relationship('category', 'name')
    ->required(),

// Bad: Manual options when relationship exists
Select::make('category_id')
    ->options(Category::pluck('name', 'id'))  // Don't do this
    ->required(),
@endboostsnippet

### Performance

1. **Eager load relationships** using `->modifyQueryUsing()`
2. **Use pagination** appropriately
3. **Index database columns** that are searchable/sortable
4. **Cache expensive operations** when possible
5. **Use queues** for long-running action tasks

### Testing

1. **Test critical paths** (create, read, update, delete)
2. **Test authorization** rules and policies
3. **Test form validation** rules
4. **Test table filters** and searches
5. **Test custom actions** behavior
6. **Use factories** for test data

## Common Pitfalls

- Using deprecated v3 method names (`actions()` instead of `recordActions()`)
- Importing actions from `Filament\Tables\Actions` instead of `Filament\Actions`
- Importing layout components from `Filament\Forms\Components` instead of `Filament\Schemas\Components`
- Using string icons instead of `Heroicon` enum
- Forgetting to authenticate in tests
- Not setting `Filament::setCurrentPanel()` in multi-panel tests
- Creating manual option arrays instead of using `relationship()`
- Not eager loading relationships (N+1 queries)
- Forgetting `deferFilters(false)` when immediate filtering is needed
- Not checking if resources appear in navigation (check `viewAny()` policy)

## Quick Reference

### Common Imports

```php
// Actions
use Filament\Actions\Action;
use Filament\Actions\{DeleteAction, EditAction, ViewAction};

// Forms
use Filament\Forms\Components\{TextInput, Select, Textarea, Toggle};
use Filament\Forms\Form;

// Tables  
use Filament\Tables\Columns\{TextColumn, ImageColumn, IconColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Table;

// Schemas (Layout)
use Filament\Schemas\Components\{Section, Grid, Fieldset, Tabs};

// Icons
use Filament\Support\Icons\Heroicon;

// Notifications
use Filament\Notifications\Notification;

// Resources
use Filament\Resources\Resource;
```

### Static Make Pattern

All Filament components use static `make()` methods:

```php
TextInput::make('name')->required()
Select::make('status')->options([...])
Section::make('Details')->schema([...])
Action::make('send')->action(fn () => ...)
```

### Method Chaining

Filament uses fluent method chaining:

```php
TextInput::make('email')
    ->label('Email Address')
    ->email()
    ->unique(ignoreRecord: true)
    ->required()
    ->maxLength(255)
```

## Additional Resources

- Use `search-docs` for detailed Filament v4 documentation
- Check `.github/instructions/` folder for comprehensive guides
- Review official Filament documentation at filamentphp.com
- Join Filament Discord community for support
- Check GitHub issues for known problems and solutions
