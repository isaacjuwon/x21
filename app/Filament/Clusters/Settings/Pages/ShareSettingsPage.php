<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\ShareHolding;
use App\Models\ShareListing;
use App\Models\ShareOrder;
use App\Models\SharePriceHistory;
use App\Settings\ShareSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
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
                        TextInput::make('holding_period_days')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_shares')
                ->label('Clear All Shares')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clear All Shares Data')
                ->modalDescription('Are you sure you want to delete all share holdings, orders, price history, and reset the share listing? This action cannot be undone. Please enter your password to confirm.')
                ->form([
                    TextInput::make('password')
                        ->password()
                        ->required()
                        ->rule('current_password'),
                ])
                ->action(function () {
                    // truncate() causes implicit DB commits which breaks DB::transaction()
                    ShareHolding::truncate();
                    ShareOrder::truncate();
                    SharePriceHistory::truncate();
                    ShareListing::query()->update([
                        'total_shares' => 0,
                        'available_shares' => 0,
                    ]);

                    Notification::make()
                        ->title('Shares Cleared')
                        ->success()
                        ->send();
                }),
        ];
    }
}
