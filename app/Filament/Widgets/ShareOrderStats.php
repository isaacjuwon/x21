<?php

namespace App\Filament\Widgets;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Models\ShareOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ShareOrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Orders', ShareOrder::where('status', ShareOrderStatus::Pending)->count())
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Buy Orders', ShareOrder::where('type', ShareOrderType::Buy)->where('status', ShareOrderStatus::Approved)->count())
                ->description('Total approved buy orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Sell Orders', ShareOrder::where('type', ShareOrderType::Sell)->where('status', ShareOrderStatus::Approved)->count())
                ->description('Total approved sell orders')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Total Volume', Number::currency(ShareOrder::where('status', ShareOrderStatus::Approved)->sum('total_amount'), Number::defaultCurrency()))
                ->description('Total trade volume (Approved)')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
