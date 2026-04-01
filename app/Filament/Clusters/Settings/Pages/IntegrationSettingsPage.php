<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\IntegrationSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
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

                Section::make('Epins Configuration')
                    ->schema([
                        TextInput::make('epins_url')
                            ->url()
                            ->default('https://api.epins.com.ng/v1'),
                        TextInput::make('epins_api_key')
                            ->password()
                            ->revealable(),
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
            ]);
    }
}
