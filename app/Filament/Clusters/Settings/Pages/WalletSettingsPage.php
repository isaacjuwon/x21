<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\WalletSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class WalletSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wallet';

    protected static string $settings = WalletSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Wallet Configuration')
                    ->schema([
                        TextInput::make('min_withdrawal')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->required(),
                        TextInput::make('withdrawal_fee')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->required(),
                        TextInput::make('stamp_duty_rate')
                            ->numeric()
                            ->suffix('%')
                            ->required(),
                        TextInput::make('stamp_duty_threshold')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
