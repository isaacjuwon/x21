<?php

namespace App\Filament\Widgets;

use App\Enums\Loans\LoanStatus;
use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class LoanStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Review', Loan::where('status', LoanStatus::Active)->count())
                ->description('Applications awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Approved', Loan::where('status', LoanStatus::Approved)->count())
                ->description('Awaiting disbursement')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make('Disbursed', Loan::where('status', LoanStatus::Disbursed)->count())
                ->description('Currently being repaid')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Disbursed', Number::currency(Loan::where('status', LoanStatus::Disbursed)->sum('principal_amount'), Number::defaultCurrency()))
                ->description('Total principal disbursed')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
