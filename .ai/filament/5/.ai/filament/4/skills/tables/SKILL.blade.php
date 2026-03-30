---
name: filament-tables-v4
description: >-
  Designs and implements Filament v4 Resource Tables. Activates when creating or updating
  Filament Resource tables; working with table columns like TextColumn, ImageColumn, IconColumn;
  adding filters (SelectFilter, TernaryFilter, custom filters); implementing actions and bulk actions;
  handling search, sort, and pagination; or writing tests for Filament tables.
---
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Filament v4 Tables

## When to Apply

Activate this skill when:
- Creating or modifying Filament Resource tables (List pages)
- Adding or refining table columns, filters, and actions
- Implementing search, sort, pagination, or bulk operations
- Displaying related data or computed columns in tables
- Writing tests for Filament tables and table interactions

## Documentation

Use `search-docs` for Filament v4 table component references, patterns, and version-specific behavior.

## Basic Usage

### Creating Resources

- Create a Resource: `{{ $assist->artisanCommand('make:filament-resource [User] --no-interaction') }}`
- Create standalone table page: `{{ $assist->artisanCommand('make:filament-page [ListItems] --no-interaction') }}`
- Multiple panels: add `--panel=[app]` when targeting a specific panel

### Fundamental Concepts

- Define tables using `Filament\Resources\Table` in Resources or `Filament\Tables\Table` for standalone components.
- Tables consist of columns, filters, actions, and bulk actions organized via `->columns()`, `->filters()`, `->recordActions()`, `->headerActions()`, and `->toolbarActions()`.
- Use `->searchable()` and `->sortable()` on columns to enable user interactions.
- In v4, filters are **deferred by default** (users click "Apply" to filter).

### File Locations (v4)

- Resource folder: `app/Filament/Resources/{PluralModel}/`
    - Main resource: `{Singular}Resource.php` (e.g., `Customers/CustomerResource.php`)
    - Pages: `Customers/Pages/ListCustomers.php`
    - Tables: `Customers/Tables/{Plural}Table.php` (referenced via static `configure()` method)
    - Columns: `Customers/Tables/Columns/` (custom column components)
    - Filters: `Customers/Tables/Filters/` (custom filter components)
- Standalone tables: `app/Livewire/` or `app/Filament/Pages/`
- Table file contains static `configure(Table $table): Table` method for organization

## Filament v4 Specifics

- **Deferred filters are default**: Users must click a button to apply filters. Use `->deferFilters(false)` to disable.
- **Action methods renamed**: Use `->recordActions([])` for row actions, `->toolbarActions([])` for bulk actions, `->headerActions([])` for header actions.
- **All action classes extend `Filament\Actions\Action`**: No more `Filament\Tables\Actions` namespace.
- **Icons use Enum**: `Filament\Support\Icons\Heroicon` enum instead of string icons.
- **Pagination 'all' option**: Not available by default; must explicitly add to `->paginated([10, 25, 50, 'all'])`.
- Layout components moved to `Filament\Schemas\Components` for form-based filters.

## Best Practices

### Resource Table Example

**Note:** By default, Filament creates a separate table file (e.g., `CustomersTable.php`) to keep resource classes organized. You can also define the table directly in the resource's `table()` method.

@boostsnippet("Resource Table Example (v4)", "php")
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Full Name')
                ->searchable()
                ->sortable()
                ->description(fn (User $record): string => $record->email)
                ->toggleable(),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'published' => 'success',
                    'archived' => 'danger',
                })
                ->sortable(),

            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ]),

            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Active Status'),
        ])
        ->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->toolbarActions([
            Tables\Actions\BulkActionGroup::make([
                DeleteAction::make(),
            ]),
        ])
        ->defaultSort('created_at', 'desc');
}
@endboostsnippet

### Standalone Table (Livewire)

**Note:** Standalone table components must implement `HasActions`, `HasSchemas`, and `HasTable` interfaces with their corresponding traits.

@boostsnippet("Standalone Table Component (v4)", "php")
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ListUsers extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
            ])
            ->filters([
                // Filters
            ])
            ->recordActions([
                // Row actions
            ])
            ->headerActions([
                // Header actions
            ]);
    }

    public function render()
    {
        return view('livewire.list-users');
    }
}
@endboostsnippet

### Common Columns

@boostsnippet("Common Table Columns (v4)", "php")
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ViewColumn;

// Text Column with features
TextColumn::make('name')
    ->searchable()
    ->sortable()
    ->copyable()
    ->wrap()
    ->placeholder('N/A')
    ->description(fn ($record) => $record->subtitle),

// Badge Column
TextColumn::make('status')
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'draft' => 'gray',
        'published' => 'success',
    }),

// Image Column
ImageColumn::make('avatar')
    ->circular()
    ->defaultImageUrl(url('/images/placeholder.png')),

// Boolean/Icon Column
IconColumn::make('is_active')
    ->boolean()
    ->sortable(),

// Toggle Column (editable)
ToggleColumn::make('is_featured')
    ->sortable()
    ->afterStateUpdated(function ($record, $state) {
        Notification::make()
            ->success()
            ->title('Updated')
            ->send();
    }),

// Custom state
TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

// Relationship data
TextColumn::make('author.name')
    ->sortable()
    ->searchable(),

// Count relationship
TextColumn::make('posts_count')
    ->counts('posts')
    ->sortable(),

// Custom view column
ViewColumn::make('custom')
    ->view('tables.columns.custom-badge'),
@endboostsnippet

### Filters (v4 Deferred by Default)

@boostsnippet("Table Filters (v4)", "php")
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

// Apply filters immediately (disable deferred)
->deferFilters(false)

// Select Filter
SelectFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
    ])
    ->multiple()
    ->searchable(),

// Filter with Relationship
SelectFilter::make('author')
    ->relationship('author', 'name')
    ->searchable()
    ->preload(),

// Ternary Filter (Boolean)
TernaryFilter::make('is_active')
    ->label('Active Status')
    ->boolean()
    ->trueLabel('Active only')
    ->falseLabel('Inactive only')
    ->queries(
        true: fn (Builder $query) => $query->where('is_active', true),
        false: fn (Builder $query) => $query->where('is_active', false),
        blank: fn (Builder $query) => $query,
    ),

// Custom Date Range Filter
Filter::make('created_at')
    ->form([
        DatePicker::make('created_from')
            ->label('From'),
        DatePicker::make('created_until')
            ->label('Until'),
    ])
    ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when(
                $data['created_from'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
            )
            ->when(
                $data['created_until'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
            );
    }),
@endboostsnippet

### Actions (v4 All Extend Filament\Actions\Action)

@boostsnippet("Table Actions (v4)", "php")
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;

// Row Actions
->recordActions([
    ViewAction::make(),
    
    EditAction::make(),
    
    DeleteAction::make(),
    
    Action::make('activate')
        ->icon(Heroicon::CheckCircle)
        ->color('success')
        ->requiresConfirmation()
        ->action(fn (User $record) => $record->activate())
        ->visible(fn (User $record): bool => !$record->is_active),
    
    // Action with Modal Form
    Action::make('send_email')
        ->icon(Heroicon::Envelope)
        ->form([
            Forms\Components\TextInput::make('subject')->required(),
            Forms\Components\Textarea::make('body')->required(),
        ])
        ->action(function (User $record, array $data): void {
            Mail::to($record)->send(new CustomEmail($data));
            
            Notification::make()
                ->success()
                ->title('Email sent')
                ->send();
        }),
    
    // Grouped Actions
    Tables\Actions\ActionGroup::make([
        ViewAction::make(),
        EditAction::make(),
        DeleteAction::make(),
    ])->icon(Heroicon::EllipsisVertical),
])

// Bulk Actions (in toolbar or header)
->toolbarActions([
    Tables\Actions\BulkActionGroup::make([
        DeleteAction::make(),
        
        Action::make('activate')
            ->label('Activate Selected')
            ->action(fn (Collection $records) => $records->each->activate())
            ->deselectRecordsAfterCompletion()
            ->color('success')
            ->requiresConfirmation(),
    ]),
])
@endboostsnippet

### Search & Sort

@boostsnippet("Search and Sort Configuration (v4)", "php")
// Basic search and sort on columns
Tables\Columns\TextColumn::make('name')
    ->searchable()
    ->sortable(),

// Individual search (separate search field)
Tables\Columns\TextColumn::make('email')
    ->searchable(isIndividual: true),

// Advanced search with custom query
Tables\Columns\TextColumn::make('name')
    ->searchable(query: function (Builder $query, string $search): Builder {
        return $query->where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%");
    }),

// Default sort
->defaultSort('created_at', 'desc')

// Persist sort in session
->persistSortInSession()
@endboostsnippet

### Pagination & Performance

@boostsnippet("Pagination and Query Optimization (v4)", "php")
// Configure pagination options
->paginated([10, 25, 50, 100])
->defaultPaginationPageOption(25)

// Enable 'all' option (not default in v4)
->paginated([10, 25, 50, 'all'])

// Persist pagination in session
->persistSearchInSession()

// Eager load relationships to prevent N+1
->modifyQueryUsing(fn (Builder $query) => $query->with(['author', 'category']))

// Scoped queries
->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()))
@endboostsnippet

### Empty States

@boostsnippet("Table Empty State (v4)", "php")
use Filament\Support\Icons\Heroicon;

->emptyStateHeading('No users yet')
->emptyStateDescription('Create your first user to get started.')
->emptyStateIcon(Heroicon::Users)
->headerActions([
    Action::make('create')
        ->label('Create user')
        ->url(route('filament.admin.resources.users.create'))
        ->icon(Heroicon::Plus),
])
@endboostsnippet

## Testing

@boostsnippet("Table Tests (v4)", "php")
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use App\Filament\Resources\UserResource\Pages\ListUsers;

it('can list users', function () {
    actingAs(User::factory()->create());
    
    $users = User::factory()->count(10)->create();
    
    livewire(ListUsers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($users)
        ->assertCountTableRecords(10);
});

it('can search users', function () {
    actingAs(User::factory()->create());
    
    $users = User::factory()->count(10)->create();
    $user = $users->first();
    
    livewire(ListUsers::class)
        ->searchTable($user->name)
        ->assertCanSeeTableRecords([$user])
        ->assertCanNotSeeTableRecords($users->skip(1));
});

it('can filter users by status', function () {
    actingAs(User::factory()->create());
    
    $activeUsers = User::factory()->count(5)->create(['is_active' => true]);
    $inactiveUsers = User::factory()->count(5)->create(['is_active' => false]);
    
    livewire(ListUsers::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords($activeUsers)
        ->assertCanNotSeeTableRecords($inactiveUsers);
});

it('can sort users', function () {
    actingAs(User::factory()->create());
    
    $users = User::factory()->count(10)->create();
    
    livewire(ListUsers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($users->sortBy('name'), inOrder: true);
});

it('can delete user', function () {
    actingAs(User::factory()->create());
    
    $user = User::factory()->create();
    
    livewire(ListUsers::class)
        ->callAction(TestAction::make('delete')->table($user))
        ->assertNotified();
    
    assertDatabaseMissing('users', ['id' => $user->id]);
});

it('can bulk delete users', function () {
    actingAs(User::factory()->create());
    
    $users = User::factory()->count(10)->create();
    
    livewire(ListUsers::class)
        ->selectTableRecords($users)
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertNotified();
    
    foreach ($users as $user) {
        assertDatabaseMissing('users', ['id' => $user->id]);
    }
});
@endboostsnippet

## Common Pitfalls

- Using deprecated method names: `->actions([])` instead of `->recordActions([])`, `->bulkActions([])` instead of `->toolbarActions([])`
- Forgetting that filters are deferred by default in v4; use `->deferFilters(false)` if immediate filtering is needed
- Importing action classes from `Filament\Tables\Actions` instead of `Filament\Actions`
- Not eager loading relationships, causing N+1 query problems
- Using string icons instead of `Filament\Support\Icons\Heroicon` enum
- Expecting 'all' pagination option to be available without explicitly adding it
- Not setting `->toggleable()` on optional columns for better UX
- Missing `->searchable()` and `->sortable()` on frequently accessed columns

## Advanced Patterns

@boostsnippet("Advanced Table Patterns (v4)", "php")
// Live polling/updates
->poll('5s')

// Custom record URL
->recordUrl(fn (User $record): string => route('users.view', $record))

// Selectable records
->selectCurrentPageOnly()

// Striped rows
->striped()

// Grouped records
->groups([
    Tables\Grouping\Group::make('status')
        ->label('Status')
        ->collapsible(),
])

// Custom header actions (e.g., for empty state)
->headerActions([
    Action::make('import')
        ->label('Import Users')
        ->icon(Heroicon::ArrowUpTray)
        ->action(fn () => redirect()->route('users.import')),
])
@endboostsnippet
