---
name: filament-actions-v4
description: >-
  Designs and implements Filament v4 Actions. Activates when adding or refining actions
  for tables, pages, forms, and infolists; customizing modals and action forms; implementing
  bulk actions and action groups; accessing selected records; and writing tests for actions.
---
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Filament v4 Actions

## When to Apply

Activate this skill when:
- Adding or modifying actions on tables (`recordActions`, `headerActions`, `toolbarActions`)
- Adding header actions to resource pages (`getHeaderActions()`)
- Building action modals with forms and confirmations
- Implementing bulk actions and action groups
- Writing tests for actions, including table and bulk actions

## Documentation

Use `search-docs` for Filament v4 actions references (overview, modals, testing patterns).

## Fundamentals

- All actions extend `Filament\Actions\Action`.
- In v4, table methods are: `->recordActions([])`, `->headerActions([])`, `->toolbarActions([])`.
- Use `Action::make('...')` with chained methods for labels, icons, colors, visibility, modals, and forms.
- Icons use `Filament\Support\Icons\Heroicon` enum.

## Basic Usage

### Row (Record) Actions in a Table

@boostsnippet("Record Actions (v4)", "php")
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->recordActions([
            EditAction::make(),
            DeleteAction::make(),
            
            Action::make('activate')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (User $record): bool => ! $record->is_active)
                ->action(fn (User $record) => $record->activate()),
        ]);
}
@endboostsnippet

### Header and Toolbar Actions in a Table

@boostsnippet("Header & Toolbar Actions (v4)", "php")
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->headerActions([
            Action::make('create')
                ->icon(Heroicon::Plus)
                ->url(route('filament.admin.resources.users.create')),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}
@endboostsnippet

### Grouping Actions

@boostsnippet("Action Groups (v4)", "php")
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;

->recordActions([
    ActionGroup::make([
        ViewAction::make(),
        EditAction::make(),
        DeleteAction::make(),
    ]),
])
@endboostsnippet

## Modals & Forms

@boostsnippet("Action Modals with Forms (v4)", "php")
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

Action::make('send_email')
    ->icon(Heroicon::Envelope)
    ->requiresConfirmation()
    ->modalHeading('Send email to user')
    ->modalSubmitActionLabel('Send')
    ->form([
        TextInput::make('subject')->required(),
        Textarea::make('body')->rows(5)->required(),
    ])
    ->action(function (User $record, array $data): void {
        Mail::to($record)->send(new CustomEmail($data));
        
        Notification::make()->success()->title('Email sent')->send();
    });
@endboostsnippet

### Column-triggered Actions

@boostsnippet("Column Actions (v4)", "php")
// Actions can be attached to a table column, turning cells into triggers.
// See docs for 'column actions' usage patterns.
@endboostsnippet

## Accessing Selected Records

@boostsnippet("Access Selected Records (v4)", "php")
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->selectable() // force table to be selectable even without bulk actions
        ->recordActions([
            Action::make('copyToSelected')
                ->accessSelectedRecords()
                ->action(function (User $record, Collection $selectedRecords): void {
                    $selectedRecords->each(fn (User $selected) => $selected->update([
                        'is_active' => $record->is_active,
                    ]));
                }),
        ]);
}
@endboostsnippet

## Page Header Actions

@boostsnippet("Page Header Actions (v4)", "php")
use Filament\Actions as Actions;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;

protected function getHeaderActions(): array
{
    return [
        Actions\ViewAction::make(),
        Actions\DeleteAction::make(),
        
        Actions\Action::make('export')
            ->icon(Heroicon::ArrowDownTray)
            ->requiresConfirmation()
            ->action(function () {
                // Export logic here
                Notification::make()->success()->title('Export started')->send();
            }),
    ];
}
@endboostsnippet

## Specialized Actions

@boostsnippet("Import/Export Actions (v4)", "php")
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->headerActions([
            ImportAction::make()
                ->importer(ProductImporter::class),
            ExportAction::make()
                ->exporter(ProductExporter::class),
        ]);
}
@endboostsnippet

## Testing Actions

@boostsnippet("Testing Table Actions (v4)", "php")
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

$invoice = Invoice::factory()->create();

livewire(ListInvoices::class)
    ->callAction(TestAction::make('send')->table($invoice));

livewire(ListInvoices::class)
    ->assertActionVisible(TestAction::make('send')->table($invoice));

livewire(ListInvoices::class)
    ->assertActionExists(TestAction::make('send')->table($invoice));
@endboostsnippet

@boostsnippet("Testing Table Header Actions (v4)", "php")
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

livewire(ListInvoices::class)
    ->callAction(TestAction::make('create')->table());

livewire(ListInvoices::class)
    ->assertActionVisible(TestAction::make('create')->table());

livewire(ListInvoices::class)
    ->assertActionExists(TestAction::make('create')->table());
@endboostsnippet

@boostsnippet("Testing Bulk Actions (v4)", "php")
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

$invoices = Invoice::factory()->count(3)->create();

livewire(ListInvoices::class)
    ->selectTableRecords($invoices->pluck('id')->toArray())
    ->callAction(TestAction::make('send')->table()->bulk());

livewire(ListInvoices::class)
    ->assertActionVisible(TestAction::make('send')->table()->bulk());

livewire(ListInvoices::class)
    ->assertActionExists(TestAction::make('send')->table()->bulk());
@endboostsnippet

@boostsnippet("Testing Page Actions (v4)", "php")
use function Pest\Livewire\livewire;

$invoice = Invoice::factory()->create();

livewire(EditInvoice::class, [ 'invoice' => $invoice ])
    ->callAction('send');

expect($invoice->refresh()->isSent())->toBeTrue();
@endboostsnippet

## Best Practices

- Prefer built-in actions (`EditAction`, `DeleteAction`, `ViewAction`) when possible
- Use `ActionGroup` to keep UI tidy with multiple actions
- Add `requiresConfirmation()` for destructive operations
- Use `Notification::make()->success()` to provide feedback
- Keep action logic concise; delegate complex tasks to services/jobs
- Use `accessSelectedRecords()` for contextual multi-record operations
- Use `->visible()` / `->hidden()` to control action visibility

## Filament v4 Specifics

- All actions extend `Filament\Actions\Action`; do not import from `Filament\Tables\Actions`
- Table action method names: `recordActions`, `headerActions`, `toolbarActions`
- Filters are deferred by default; this affects actions that rely on filtered selections
- Icons use `Heroicon` enum; avoid string icons

## Common Pitfalls

- Using deprecated method names: `->actions([])` or `->bulkActions([])` in tables
- Importing action classes from `Filament\Tables\Actions` (use `Filament\Actions`)
- Forgetting to use `TestAction` in tests for table actions
- Not adding confirmation for destructive actions
- Ignoring notifications for user feedback
- Overloading actions with business logic (move to services/jobs)

## Quick Reference

@boostsnippet("Common Action Imports (v4)", "php")
use Filament\Actions\{Action, EditAction, DeleteAction, ViewAction, ActionGroup, BulkActionGroup, DeleteBulkAction};
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
@endboostsnippet
