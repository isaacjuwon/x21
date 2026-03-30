<?php

namespace App\Filament\Resources\Wallets\Pages;

use App\Filament\Resources\Wallets\WalletResource;
use App\Models\Wallet;
use App\Enums\Wallets\WalletType;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class ViewWallet extends ViewRecord
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deposit')
                ->label('Manual Deposit')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->prefix(Number::defaultCurrency())
                        ->minValue(1)
                        ->required(),
                    Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Reason for manual deposit')
                        ->required(),
                ])
                ->action(function (array $data, Wallet $record): void {
                    $record->user->deposit((float) $data['amount'], $record->type, $data['notes']);

                    Notification::make()
                        ->success()
                        ->title('Deposit successful')
                        ->body(Number::currency($data['amount'], Number::defaultCurrency()) . " deposited into {$record->user->name}'s {$record->type->getLabel()} wallet.")
                        ->send();
                }),

            Action::make('withdraw')
                ->label('Manual Withdrawal')
                ->icon('heroicon-o-minus-circle')
                ->color('danger')
                ->form([
                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->prefix(Number::defaultCurrency())
                        ->minValue(1)
                        ->required(),
                    Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Reason for manual withdrawal')
                        ->required(),
                ])
                ->action(function (array $data, Wallet $record): void {
                    try {
                        $record->user->withdraw((float) $data['amount'], $record->type, $data['notes']);

                        Notification::make()
                            ->success()
                            ->title('Withdrawal successful')
                            ->body(Number::currency($data['amount'], Number::defaultCurrency()) . " withdrawn from {$record->user->name}'s {$record->type->getLabel()} wallet.")
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Withdrawal failed')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Wallet Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('user.name')->label('Owner'),
                            TextEntry::make('user.email')->label('Email'),
                            TextEntry::make('type')->label('Wallet Type')->badge(),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('balance')
                                ->label('Total Balance')
                                ->money(fn() => Number::defaultCurrency())
                                ->weight('bold'),
                            TextEntry::make('held_balance')
                                ->label('Held Funds')
                                ->money(fn() => Number::defaultCurrency())
                                ->color('warning'),
                            TextEntry::make('available_balance')
                                ->label('Available to Use')
                                ->money(fn() => Number::defaultCurrency())
                                ->color('success')
                                ->weight('bold'),
                        ]),
                    ]),
            ]);
    }
}
