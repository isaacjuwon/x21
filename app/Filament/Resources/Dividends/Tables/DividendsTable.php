<?php

namespace App\Filament\Resources\Dividends\Tables;

use App\Actions\Shares\DeclareDividendAction;
use App\Enums\Shares\DividendStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class DividendsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('percentage')
                    ->label('Percentage')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('share_price')
                    ->label('Share Price')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Distributed')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('declared_at')
                    ->label('Declared At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('payouts_count')
                    ->label('Payouts')
                    ->counts('payouts')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(DividendStatus::class),
            ])
            ->headerActions([
                Action::make('declare')
                    ->label('Declare Dividend')
                    ->icon(Heroicon::CurrencyDollar)
                    ->form([
                        TextInput::make('percentage')
                            ->label('Dividend Percentage')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0.01)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        app(DeclareDividendAction::class)->handle((float) $data['percentage']);
                        Notification::make()->success()->title('Dividend declared and queued for distribution')->send();
                    }),
            ])
            ->defaultSort('declared_at', 'desc');
    }
}
