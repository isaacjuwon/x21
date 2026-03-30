---
name: filament-widgets-v4
description: >-
  Designs and implements Filament v4 Widgets for dashboards and resource pages.
  Activates when creating custom widgets, stat widgets, chart widgets, table widgets,
  configuring widget grids, and writing tests for widgets.
---
@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Filament v4 Widgets

## When to Apply

Activate this skill when:
- Creating custom widgets for dashboards or resource pages
- Building stat widgets to display metrics
- Creating chart widgets using Chart.js
- Building table widgets to display data tables
- Configuring widget columns and layout
- Writing tests for widget functionality

## Documentation

Use `search-docs` for Filament v4 widgets references (custom widgets, stat widgets, chart widgets, table widgets, widget configuration, testing).

## Widget Fundamentals

Widgets are Livewire components that display data on dashboards or resource pages. They extend `Filament\Widgets\Widget` and have both a PHP class and a Blade view.

@boostsnippet("Creating a Custom Widget", "bash")
php artisan make:filament-widget BlogPostsOverview
@endboostsnippet

This creates a widget class in `app/Filament/Widgets/` and a view in `resources/views/filament/widgets/`.

@boostsnippet("Custom Widget Class Structure (v4)", "php")
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class BlogPostsOverview extends Widget
{
    protected static ?string $heading = 'Blog Posts Overview';

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    public function render()
    {
        return view('filament.widgets.blog-posts-overview');
    }
}
@endboostsnippet


@boostsnippet("Custom Widget Blade View", "blade")
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Blog Posts') }}
        </x-slot>

        <div class="space-y-4">
            <p>Total posts: {{ $this->getTotalPosts() }}</p>
            <p>Published: {{ $this->getPublishedCount() }}</p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
@endboostsnippet

## Registering Widgets on Dashboards

@boostsnippet("Register Widget on Dashboard", "php")
// In app/Filament/Pages/Dashboard.php
use App\Filament\Widgets\BlogPostsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getWidgets(): array
    {
        return [
            BlogPostsOverview::class,
        ];
    }
}
@endboostsnippet

## Registering Widgets on Resource Pages

@boostsnippet("Register Widget on Resource Page", "php")
// In app/Filament/Resources/CustomerResource.php
use App\Filament\Resources\Customers\Widgets\CustomerOverview;

public static function getWidgets(): array
{
    return [
        CustomerOverview::class,
    ];
}
@endboostsnippet

Create the resource widget with:

@boostsnippet("Creating a Resource Widget", "bash")
php artisan make:filament-widget CustomerOverview --resource=CustomerResource
@endboostsnippet

## Passing Properties to Widgets

@boostsnippet("Pass Properties to Widgets (v4)", "php")
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function getWidgets(): array
    {
        return [
            CustomerResource\Widgets\CustomerOverview::make([
                'status' => 'active',
                'limit' => 10,
            ]),
        ];
    }
}
@endboostsnippet

Access properties in the widget class using public Livewire properties:

@boostsnippet("Access Widget Properties", "php")
use Filament\Widgets\Widget;

class CustomerOverview extends Widget
{
    public string $status = 'all';
    
    public int $limit = 5;

    public function render()
    {
        $customers = Customer::where('status', $this->status)
            ->limit($this->limit)
            ->get();

        return view('filament.widgets.customer-overview', [
            'customers' => $customers,
        ]);
    }
}
@endboostsnippet

## Stat Widgets

Stat widgets display metrics with optional trend icons.

@boostsnippet("Creating a Stat Widget", "bash")
php artisan make:filament-widget BlogPostsStats --stat
@endboostsnippet

@boostsnippet("Stat Widget Implementation (v4)", "php")
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogPostsStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', BlogPost::count())
                ->description('All blog posts')
                ->icon('heroicon-o-document-text')
                ->color('success'),
            
            Stat::make('Published', BlogPost::where('published', true)->count())
                ->description('Published this month')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->trend('up')
                ->trendDirection('down'),

            Stat::make('Drafts', BlogPost::where('published', false)->count())
                ->description('Waiting to publish')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),
        ];
    }
}
@endboostsnippet

## Chart Widgets

Chart widgets use Chart.js to display interactive charts.

@boostsnippet("Creating a Chart Widget", "bash")
php artisan make:filament-widget BlogPostsChart --chart
@endboostsnippet

@boostsnippet("Chart Widget Implementation (v4)", "php")
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Blog Posts per Month';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Posts Created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                    'fill' => 'start',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
@endboostsnippet

### Chart Types

@boostsnippet("Chart Widget Types", "php")
// Available types:
protected function getType(): string
{
    return 'line';      // Line chart
    return 'bar';       // Bar chart
    return 'pie';       // Pie chart
    return 'doughnut';  // Doughnut chart
    return 'radar';     // Radar chart
    return 'polar';     // Polar chart
}
@endboostsnippet

## Table Widgets

@boostsnippet("Creating a Table Widget", "bash")
php artisan make:filament-widget LatestBlogPosts --table
@endboostsnippet

@boostsnippet("Table Widget Implementation (v4)", "php")
<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\BlogPost;

class LatestBlogPosts extends BaseWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(BlogPost::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('published'),
            ])
            ->paginated([5, 10, 25]);
    }
}
@endboostsnippet

## Customizing the Dashboard Page

@boostsnippet("Customize Dashboard Grid Layout (v4)", "php")
<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected int | string | array $columnSpan = 'full';

    // Or set specific column spans for widgets
    public function getColumns(): int | string | array
    {
        return [
            'md' => 2,
            'lg' => 3,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            BlogPostsStats::class,      // Default: spans 1 column
            BlogPostsChart::class,      // Default: spans 1 column
            LatestBlogPosts::class,     // Default: spans full width
        ];
    }
}
@endboostsnippet

### Widget Column Spans

@boostsnippet("Set Widget Column Span", "php")
use Filament\Widgets\Widget;

class BlogPostsOverview extends Widget
{
    protected static ?string $columnSpan = 'full';  // Full width
    
    // Or:
    protected static ?string $columnSpan = '1/2';   // Half width
    protected static ?string $columnSpan = '1/3';   // One third width
}
@endboostsnippet

## Lazy Loading Widgets

Enable lazy loading for widgets that load data asynchronously:

@boostsnippet("Enable Lazy Loading (v4)", "php")
use Filament\Widgets\Widget;

class ExpensiveWidget extends Widget
{
    protected static bool $isLazy = true;

    public function render()
    {
        return view('filament.widgets.expensive-widget', [
            'data' => $this->loadExpensiveData(),
        ]);
    }

    private function loadExpensiveData()
    {
        // This will load asynchronously
        return BlogPost::with('comments', 'author')
            ->wherePivot('featured', true)
            ->get();
    }
}
@endboostsnippet

## Testing Widgets

Widgets are Livewire components, so test them using the `livewire()` helper:

@boostsnippet("Testing Widget Rendering", "php")
use App\Filament\Widgets\BlogPostsOverview;
use function Pest\Livewire\livewire;

it('renders blog posts overview widget', function () {
    livewire(BlogPostsOverview::class)
        ->assertSuccessful();
});
@endboostsnippet

@boostsnippet("Testing Widget Data", "php")
use App\Filament\Widgets\BlogPostsStats;
use function Pest\Livewire\livewire;

it('displays correct post statistics', function () {
    BlogPost::factory()->count(5)->create(['published' => true]);
    BlogPost::factory()->count(3)->create(['published' => false]);

    $stats = livewire(BlogPostsStats::class)
        ->getStats();

    expect($stats[0]->getValue())->toBe(8);  // Total posts
    expect($stats[1]->getValue())->toBe(5);  // Published
    expect($stats[2]->getValue())->toBe(3);  // Drafts
});
@endboostsnippet

@boostsnippet("Testing Table Widget", "php")
use App\Filament\Widgets\LatestBlogPosts;
use App\Models\BlogPost;
use function Pest\Livewire\livewire;

it('displays latest blog posts in table widget', function () {
    $posts = BlogPost::factory()->count(3)->create();

    livewire(LatestBlogPosts::class)
        ->assertCanSeeTableRecords($posts)
        ->assertTableColumnVisible('title');
});
@endboostsnippet

@boostsnippet("Testing Widget Properties", "php")
use App\Filament\Widgets\CustomerOverview;
use function Pest\Livewire\livewire;

it('accepts and uses widget properties', function () {
    livewire(CustomerOverview::class, [
        'status' => 'active',
        'limit' => 10,
    ])
        ->assertSet('status', 'active')
        ->assertSet('limit', 10);
});
@endboostsnippet

## v4 Specifics

- All widgets extend `Filament\Widgets\Widget`
- Stat widgets use `StatsOverviewWidget` and return an array of `Stat` instances
- Chart widgets extend `ChartWidget` with `getData()` and `getType()` methods
- Table widgets extend `TableWidget` and define a `table()` method
- Lazy loading via `protected static bool $isLazy = true` loads widget asynchronously
- Column spans: `'full'`, `'1/2'`, `'1/3'`, etc., or use responsive arrays `['md' => 2, 'lg' => 3]`
- Widgets are Livewire components, so use `livewire()` helper for testing
- Icons use the `Filament\Support\Icons\Heroicon` enum

## Best Practices

- Use lazy loading for widgets that perform expensive queries
- Keep widget data queries optimized; consider using `select()` to limit columns
- Set appropriate sort order with `protected static ?int $sort` for dashboard organization
- Use stat widget trends to show metric changes over time
- Always add descriptions to stat widgets for context
- Group related metrics in stat widgets rather than creating separate widgets
- Test widget rendering and data independently from dashboard page tests
- Use proper heading text to describe widget purpose

## Common Pitfalls

- Forgetting to include `@livewire('notifications')` in layout when widgets send notifications
- Not lazy loading widgets with expensive queries, causing dashboard to load slowly
- Rendering too many widgets on the dashboard without pagination
- Not registering custom widgets in `getWidgets()` method
- Attempting to use table actions in table widgets without proper permission checks
- Forgetting that widgets are Livewire components, so use livewire test helper
- Not setting appropriate column spans, causing layout issues on different screen sizes

## Quick Reference

@boostsnippet("Widget Artisan Commands", "bash")
# Custom widget
php artisan make:filament-widget WidgetName

# Stat widget
php artisan make:filament-widget WidgetName --stat

# Chart widget
php artisan make:filament-widget WidgetName --chart

# Table widget
php artisan make:filament-widget WidgetName --table

# Resource widget
php artisan make:filament-widget WidgetName --resource=ResourceName
@endboostsnippet

@boostsnippet("Widget Base Imports", "php")
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\TableWidget;
@endboostsnippet
