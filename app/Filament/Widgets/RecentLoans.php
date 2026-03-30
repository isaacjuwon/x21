<?php

namespace App\Filament\Widgets;

use App\Enums\Loans\LoanStatus;
use App\Models\Loan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Number;

class RecentLoans extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Loan::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Borrower')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('principal_amount')
                    ->label('Principal')
                    ->money(fn() => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('outstanding_balance')
                    ->label('Balance')
                    ->money(fn() => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('interest_rate')
                    ->label('Rate')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
