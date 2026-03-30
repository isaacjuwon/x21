<?php

namespace App\Filament\Resources\Wallets\Tables;

use App\Models\Wallet;
use App\Enums\Wallets\WalletType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Number;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Total Balance')
                    ->money(fn() => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('held_balance')
                    ->label('Held')
                    ->money(fn() => Number::defaultCurrency())
                    ->toggleable(),

                TextColumn::make('available_balance')
                    ->label('Available')
                    ->money(fn() => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(WalletType::class),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
