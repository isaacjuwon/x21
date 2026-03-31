<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Enums\Loans\InterestMethod;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\LoanSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class LoanSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string $settings = LoanSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Configuration')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('min_amount')
                                ->numeric()
                                ->prefix(Number::defaultCurrency())
                                ->required(),
                            TextInput::make('max_amount')
                                ->numeric()
                                ->prefix(Number::defaultCurrency())
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('default_interest_rate')
                                ->numeric()
                                ->suffix('%')
                                ->required(),
                            Select::make('interest_method')
                                ->options(InterestMethod::class)
                                ->required(),
                        ]),
                        Toggle::make('auto_approve')
                            ->label('Auto-approve Applications'),
                    ]),

                Section::make('Eligibility Enforcement')
                    ->description('Enable or disable specific loan eligibility rules.')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('enforce_min_account_age')
                                ->label('Enforce Minimum Account Age')
                                ->helperText('Users must have an account for a certain period.'),
                            TextInput::make('min_account_age_days')
                                ->label('Min Account Age (Days)')
                                ->numeric()
                                ->required(),
                        ]),
                        Toggle::make('enforce_loan_level_limits')
                            ->label('Enforce Loan Level Limits')
                            ->helperText('Restrict loan amounts based on user loan levels.'),
                        Toggle::make('enforce_shares_requirement')
                            ->label('Enforce Share Requirement')
                            ->helperText('Users must hold a percentage of shares relative to the loan amount.'),
                        TextInput::make('min_shares_percentage')
                            ->label('Min Shares Percentage')
                            ->numeric()
                            ->suffix('%')
                            ->required(),
                        Toggle::make('enforce_kyc_requirement')
                            ->label('Enforce KYC Requirement')
                            ->helperText('Require users to complete NIN/BVN verification.'),
                    ]),
            ]);
    }
}
