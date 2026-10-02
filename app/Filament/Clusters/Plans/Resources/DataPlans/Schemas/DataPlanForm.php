<?php

namespace App\Filament\Clusters\Plans\Resources\DataPlans\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class DataPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('e.g. 1GB Daily'),

                            Select::make('brand_id')
                                ->relationship('brand', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('type')
                                ->required()
                                ->placeholder('e.g. SME, GIFTING, CORPORATE')
                                ->hint('Used to group plans by category on the purchase page'),

                            TextInput::make('duration')
                                ->required()
                                ->placeholder('e.g. 30 Days, 7 Days, 1 Month')
                                ->hint('Validity period shown to the user'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('price')
                                ->numeric()
                                ->prefix(Number::defaultCurrency())
                                ->required()
                                ->minValue(0.01),

                            Toggle::make('status')
                                ->label('Active')
                                ->default(true),
                        ]),

                        Repeater::make('providerCodes')
                            ->label('Provider API Codes')
                            ->relationship()
                            ->schema([
                                Select::make('provider')
                                    ->options(
                                        collect(config('api.providers.failover.providers', ['vtugate']))
                                            ->mapWithKeys(fn (string $provider): array => [$provider => ucfirst($provider)])
                                            ->all()
                                    )
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->label('Provider'),
                                TextInput::make('code')
                                    ->required()
                                    ->placeholder('e.g. VTG_MTN_1GB  or  mtn-1gb-1000'),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->addActionLabel('Add API code')
                            ->itemLabel(
                                fn (array $state): ?string => isset($state['provider'])
                                    ? strtoupper($state['provider'])
                                    : null
                            ),
                    ]),
            ]);
    }
}
