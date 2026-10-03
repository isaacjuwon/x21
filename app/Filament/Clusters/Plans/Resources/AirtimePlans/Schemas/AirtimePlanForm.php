<?php

namespace App\Filament\Clusters\Plans\Resources\AirtimePlans\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
class AirtimePlanForm
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
                                ->placeholder('e.g. MTN Airtime'),

                            Select::make('brand_id')
                                ->relationship('brand', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('type')
                                ->nullable()
                                ->placeholder('e.g. VTU, Ported')
                                ->hint('Used to group plans by category on the purchase page'),

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
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->label('Provider'),
                                TextInput::make('code')
                                    ->required()
                                    ->placeholder('e.g. mtn  or  MTN_VTU'),
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
