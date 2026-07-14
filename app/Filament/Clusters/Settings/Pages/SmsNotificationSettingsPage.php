<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\SmsSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SmsNotificationSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftEllipsis;

    protected static string $settings = SmsSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'SMS Notifications';

    protected static ?string $title = 'SMS Notification Settings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Loan Notifications')
                    ->description('Send an SMS when a loan status changes.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('sms_loan_approved')
                            ->label('Loan Approved'),
                        Toggle::make('sms_loan_disbursed')
                            ->label('Loan Disbursed'),
                        Toggle::make('sms_loan_rejected')
                            ->label('Loan Rejected'),
                        Toggle::make('sms_loan_settled')
                            ->label('Loan Fully Settled'),
                    ]),

                Section::make('Share Notifications')
                    ->description('Send an SMS for share order and dividend events.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('sms_share_order_approved')
                            ->label('Share Order Approved'),
                        Toggle::make('sms_share_order_rejected')
                            ->label('Share Order Rejected'),
                        Toggle::make('sms_dividend_paid')
                            ->label('Dividend Paid'),
                    ]),

                Section::make('Wallet Notifications')
                    ->description('Send an SMS for wallet events.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('sms_transaction_reversed')
                            ->label('Transaction Reversed / Refunded'),
                        Toggle::make('sms_wallet_withdrawn')
                            ->label('Withdrawal Initiated'),
                    ]),

                Section::make('Service Notifications')
                    ->description('Send an SMS after a successful VTU purchase (airtime, data, cables, etc.).')
                    ->columns(2)
                    ->schema([
                        Toggle::make('sms_service_purchased')
                            ->label('Service Purchase Successful'),
                    ]),

                Section::make('Support Notifications')
                    ->description('Send an SMS for support ticket events.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('sms_ticket_created')
                            ->label('Ticket Created'),
                    ]),
            ]);
    }
}
