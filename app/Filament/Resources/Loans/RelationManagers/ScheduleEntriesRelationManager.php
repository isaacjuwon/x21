<?php

namespace App\Filament\Resources\Loans\RelationManagers;

use App\Enums\Loans\LoanScheduleEntryStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ScheduleEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'scheduleEntries';

    protected static ?string $title = 'Repayment Schedule';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('instalment_number')
            ->columns([
                TextColumn::make('instalment_number')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('instalment_amount')
                    ->label('Instalment')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('principal_component')
                    ->label('Principal')
                    ->money(fn () => Number::defaultCurrency()),

                TextColumn::make('interest_component')
                    ->label('Interest')
                    ->money(fn () => Number::defaultCurrency()),

                TextColumn::make('outstanding_balance')
                    ->label('Balance After')
                    ->money(fn () => Number::defaultCurrency()),

                TextColumn::make('remaining_amount')
                    ->label('Remaining')
                    ->money(fn () => Number::defaultCurrency()),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(LoanScheduleEntryStatus::class),
            ])
            ->defaultSort('instalment_number')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
