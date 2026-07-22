<?php

namespace App\Filament\Widgets;

use App\Enums\Loans\LoanStatus;
use App\Enums\Shares\ShareOrderStatus;
use App\Models\Dividend;
use App\Models\Loan;
use App\Models\ShareOrder;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered members')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Online Users', DB::table('sessions')->whereNotNull('user_id')->where('last_activity', '>=', now()->subMinutes(15)->getTimestamp())->count())
                ->description('Active in the last 15 mins')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('success'),

            Stat::make('Online Guests', DB::table('sessions')->whereNull('user_id')->where('last_activity', '>=', now()->subMinutes(15)->getTimestamp())->count())
                ->description('Active in the last 15 mins')
                ->descriptionIcon('heroicon-m-user')
                ->color('gray'),

            Stat::make('Active Applications', Loan::where('status', LoanStatus::Active)->count())
                ->description('Loans awaiting approval')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Loans Being Repaid', Loan::where('status', LoanStatus::Disbursed)->count())
                ->description('Currently disbursed and active')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Share Orders', ShareOrder::where('status', ShareOrderStatus::Pending)->count())
                ->description('Awaiting admin approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Dividends', Number::currency(Dividend::sum('total_amount'), Number::defaultCurrency()))
                ->description('Total dividends declared')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('primary'),
        ];
    }
}
