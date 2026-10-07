<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\IntegrationSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntegrationSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string $settings = IntegrationSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('VTU Failover Configuration')
                    ->description('Configure active VTU providers and their failover execution priority.')
                    ->schema([
                        Select::make('vtu_failover_providers')
                            ->label('Active Providers & Priority Order')
                            ->helperText('Select providers and drag to arrange in order of priority (first = primary, subsequent = failover fallbacks).')
                            ->multiple()
                            ->reorderable()
                            ->options([
                                'vtugate' => 'VTUgate',
                                'vtpass' => 'VTPass',
                            ])
                            ->default(['vtugate', 'vtpass'])
                            ->required(),
                    ]),

                Section::make('Paystack Configuration')
                    ->schema([
                        TextInput::make('paystack_url')
                            ->url()
                            ->default('https://api.paystack.co'),
                        Grid::make(2)->schema([
                            TextInput::make('paystack_public_key')
                                ->password()
                                ->revealable(),
                            TextInput::make('paystack_secret_key')
                                ->password()
                                ->revealable(),
                        ]),
                    ]),

                Section::make('Dojah Configuration')
                    ->schema([
                        TextInput::make('dojah_base_url')
                            ->url()
                            ->default('https://api.dojah.io'),
                        Grid::make(2)->schema([
                            TextInput::make('dojah_app_id')
                                ->password()
                                ->revealable(),
                            TextInput::make('dojah_api_key')
                                ->password()
                                ->revealable(),
                        ]),
                    ]),

                Section::make('Vtugate Configuration')
                    ->description('Credentials for Vtugate VTU and bill payment services.')
                    ->schema([
                        TextInput::make('vtugate_url')
                            ->label('Base URL')
                            ->url()
                            ->default('https://api.vtugate.com'),
                        TextInput::make('vtugate_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable(),
                    ]),

                Section::make('VTPass Configuration')
                    ->description('Credentials for VTPass VTU and bill payment services.')
                    ->schema([
                        TextInput::make('vtpass_url')
                            ->label('Base URL')
                            ->url()
                            ->default('https://vtpass.com/api'),
                        Grid::make(3)->schema([
                            TextInput::make('vtpass_api_key')
                                ->label('API Key')
                                ->password()
                                ->revealable(),
                            TextInput::make('vtpass_secret_key')
                                ->label('Secret Key')
                                ->password()
                                ->revealable(),
                            TextInput::make('vtpass_public_key')
                                ->label('Public Key')
                                ->password()
                                ->revealable(),
                        ]),
                    ]),

                Section::make('OpenAI Configuration')
                    ->description('Used for the AI support assistant.')
                    ->schema([
                        TextInput::make('openai_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable(),
                        TextInput::make('openai_model')
                            ->label('Model')
                            ->placeholder('gpt-4o-mini'),
                    ]),

                Section::make('KudiSMS Configuration')
                    ->description('Used for sending SMS notifications.')
                    ->schema([
                        TextInput::make('kudisms_url')
                            ->label('Base URL')
                            ->url()
                            ->default('https://api.kudisms.net'),
                        Grid::make(2)->schema([
                            TextInput::make('kudisms_api_key')
                                ->label('API Key')
                                ->password()
                                ->revealable(),
                            TextInput::make('kudisms_sender_id')
                                ->label('Sender ID')
                                ->placeholder('YOURAPP'),
                        ]),
                    ]),
            ]);
    }
}
