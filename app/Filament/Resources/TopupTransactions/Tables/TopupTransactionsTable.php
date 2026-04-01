<?php

namespace App\Filament\Resources\TopupTransactions\Tables;

use App\Enums\Topups\TopupTransactionStatus;
use App\Enums\Topups\TopupType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class TopupTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('recipient')
                    ->label('Recipient')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(TopupType::class)
                    ->multiple(),

                SelectFilter::make('status')
                    ->options(TopupTransactionStatus::class)
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('user'));
    }
}
