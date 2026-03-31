<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\ShareSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class ShareSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string $settings = ShareSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Share Configuration')
                    ->schema([
                        TextInput::make('price_per_share')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->required(),
                        TextInput::make('min_shares_purchase')
                            ->numeric()
                            ->required(),
                        TextInput::make('max_shares_per_user')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
