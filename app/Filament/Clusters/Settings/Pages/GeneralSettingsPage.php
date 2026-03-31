<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\GeneralSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GeneralSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site Configuration')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('site_name')
                                ->required(),
                            Textarea::make('site_description')
                                ->rows(3),
                        ]),
                    ]),

                Section::make('Assets')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('site_logo')
                                ->image()
                                ->directory('settings')
                                ->visibility('public')
                                ->imageEditor(),
                            FileUpload::make('site_dark_logo')
                                ->image()
                                ->directory('settings')
                                ->visibility('public')
                                ->imageEditor(),
                        ]),
                        Grid::make(2)->schema([
                            FileUpload::make('site_favicon')
                                ->image()
                                ->directory('settings')
                                ->visibility('public')
                                ->imageEditor(),
                            FileUpload::make('site_dark_favicon')
                                ->image()
                                ->directory('settings')
                                ->visibility('public')
                                ->imageEditor(),
                        ]),
                    ]),

                Section::make('Contact Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('contact_email')
                                ->email(),
                            TextInput::make('support_email')
                                ->email(),
                        ]),
                    ]),

                Section::make('Localization & System')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('currency')
                                ->options([
                                    'NGN' => 'Nigerian Naira (₦)',
                                    'USD' => 'US Dollar ($)',
                                    'GBP' => 'British Pound (£)',
                                    'EUR' => 'Euro (€)',
                                ])
                                ->required(),
                            TextInput::make('timezone')
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            Toggle::make('registration_enabled')
                                ->label('Enable Registration'),
                            Toggle::make('maintenance_mode')
                                ->label('Maintenance Mode'),
                        ]),
                    ]),
            ]);
    }
}
