<?php

declare(strict_types=1);

namespace App\Filament\Resources\Plans\Pages;

use App\Enums\Plans\ServiceType;
use App\Filament\Resources\Plans\PlanResource;
use App\Models\Plan;
use App\Services\Vtu\PlanSyncService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncPlans')
                ->label('Sync Upstream Plans')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->schema([
                    Select::make('provider')
                        ->label('Provider')
                        ->options([
                            'all' => 'All Providers (VtuGate & VTPass)',
                            'vtugate' => 'VtuGate',
                            'vtpass' => 'VTPass',
                        ])
                        ->default('all')
                        ->required(),
                    Select::make('service_type')
                        ->label('Service Type')
                        ->options([
                            'all' => 'All Services',
                            'data' => 'Data',
                            'cable' => 'Cable TV',
                            'education' => 'Education',
                        ])
                        ->default('all')
                        ->required(),
                ])
                ->action(function (array $data, PlanSyncService $syncService): void {
                    $provider = $data['provider'] ?? 'all';
                    $srv = $data['service_type'] ?? 'all';

                    $serviceType = match ($srv) {
                        'data' => ServiceType::Data,
                        'cable' => ServiceType::Cable,
                        'education' => ServiceType::Education,
                        default => null,
                    };

                    if ($provider === 'all') {
                        $reports = $syncService->syncAll($serviceType);
                        $created = array_sum(array_map(fn ($r) => $r->created, $reports));
                        $merged = array_sum(array_map(fn ($r) => $r->merged, $reports));
                    } else {
                        $report = $syncService->syncProvider($provider, $serviceType);
                        $created = $report->created;
                        $merged = $report->merged;
                    }

                    Notification::make()
                        ->title('Plans synchronized successfully')
                        ->body("Created {$created} new plans, merged {$merged} provider codes.")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $counts = Plan::query()
            ->selectRaw('service_type, count(*) as count')
            ->groupBy('service_type')
            ->pluck('count', 'service_type');

        $totalCount = (int) $counts->sum();

        $tabs = [
            'all' => Tab::make('All Plans')
                ->badge($totalCount),
        ];

        foreach (ServiceType::cases() as $serviceType) {
            $count = (int) ($counts[$serviceType->value] ?? 0);

            $tabs[$serviceType->value] = Tab::make($serviceType->getLabel())
                ->badge($count)
                ->badgeColor($serviceType->getColor())
                ->icon($serviceType->getIcon())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('service_type', $serviceType));
        }

        return $tabs;
    }
}
