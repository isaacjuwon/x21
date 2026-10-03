<?php

namespace App\Filament\Clusters\Plans\Resources\ElectricityPlans\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
class ElectricityPlanForm
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
                                ->placeholder('e.g. Ikeja Prepaid'),

                            Select::make('brand_id')
                                ->relationship('brand', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('type')
                                ->required()
                                ->placeholder('e.g. Prepaid, Postpaid')
                                ->hint('Used to group plans by category on the purchase page'),
                        ]),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),

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
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->label('Provider'),
                                TextInput::make('code')
                                    ->required()
                                    ->placeholder('e.g. PREPAID_IBEDC  or  prepaid'),
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
