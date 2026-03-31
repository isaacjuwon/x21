<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\LayoutSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LayoutSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static string $settings = LayoutSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Appearance')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->required(),
                        Select::make('font_family')
                            ->options([
                                'Inter' => 'Inter',
                                'Roboto' => 'Roboto',
                                'Open Sans' => 'Open Sans',
                            ])
                            ->required(),
                        Toggle::make('sidebar_collapsible')
                            ->label('Collapsible Sidebar'),
                    ])->columns(2),

                Section::make('Homepage Header')
                    ->schema([
                        TextInput::make('homepage_title')
                            ->label('Hero Title')
                            ->required(),
                        Textarea::make('homepage_description')
                            ->label('Hero Description')
                            ->rows(3)
                            ->required(),
                        TextInput::make('banner')
                            ->label('Banner Image URL'),
                    ]),

                Section::make('Homepage Features')
                    ->schema([
                        TextInput::make('homepage_features_title')
                            ->required(),
                        Textarea::make('homepage_features_description')
                            ->rows(3)
                            ->required(),
                        Repeater::make('homepage_features_items')
                            ->schema([
                                TextInput::make('title')->required(),
                                Textarea::make('description')->required(),
                                TextInput::make('icon')->label('Heroicon Name'),
                            ])
                            ->columns(2)
                            ->grid(2),
                    ]),

                Section::make('Footer & Social')
                    ->schema([
                        Textarea::make('about')
                            ->label('About Us Text')
                            ->rows(3),
                        TextInput::make('address'),
                        TextInput::make('email')
                            ->email(),
                        TextInput::make('facebook')
                            ->url(),
                        TextInput::make('twitter')
                            ->url(),
                        TextInput::make('instagram')
                            ->url(),
                    ])->columns(2),
            ]);
    }
}
