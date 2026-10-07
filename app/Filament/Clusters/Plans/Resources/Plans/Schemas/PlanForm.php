<?php

namespace App\Filament\Clusters\Plans\Resources\Plans\Schemas;

use App\Enums\Plans\ServiceType;
use App\Settings\IntegrationSettings;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;
use Throwable;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan Details')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('service_type')
                                ->label('Service Type')
                                ->options(
                                    collect(ServiceType::cases())
                                        ->mapWithKeys(fn (ServiceType $type): array => [$type->value => $type->getLabel()])
                                        ->all()
                                )
                                ->required()
                                ->searchable(),

                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('e.g. MTN 1GB Monthly or DSTV Compact'),

                            Select::make('brand_id')
                                ->label('Brand / Network')
                                ->relationship('brand', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('type')
                                ->nullable()
                                ->placeholder('e.g. SME, CORPORATE, GIFTING, VTU')
                                ->hint('Used to group plans by category on purchase page'),

                            TextInput::make('duration')
                                ->nullable()
                                ->placeholder('e.g. 30 Days, 1 Month')
                                ->hint('Validity period shown to user'),

                            TextInput::make('api_code')
                                ->label('Internal / Fallback API Code')
                                ->nullable()
                                ->placeholder('e.g. MTN_1GB_CODE')
                                ->hint('Default fallback code if provider code is not set'),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('price')
                                ->label('Selling Price')
                                ->numeric()
                                ->prefix(Number::defaultCurrency())
                                ->required()
                                ->minValue(0),

                            TextInput::make('cost_price')
                                ->label('Cost Price')
                                ->numeric()
                                ->prefix(Number::defaultCurrency())
                                ->nullable()
                                ->hint('Wholesale cost from provider'),

                            Toggle::make('status')
                                ->label('Active')
                                ->default(true),
                        ]),

                        Repeater::make('providerCodes')
                            ->label('Provider API Codes')
                            ->relationship()
                            ->schema([
                                Select::make('provider')
                                    ->options(function (): array {
                                        $providers = null;
                                        try {
                                            $providers = app(IntegrationSettings::class)->vtu_failover_providers;
                                        } catch (Throwable) {
                                        }

                                        $list = $providers ?? config('api.providers.failover.providers', ['vtugate', 'vtpass']);

                                        return collect($list)
                                            ->mapWithKeys(fn (string $provider): array => [$provider => ucfirst($provider)])
                                            ->all();
                                    })
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->label('Provider'),
                                TextInput::make('code')
                                    ->required()
                                    ->placeholder('e.g. mtn or mtn-1000 or dstv-compact'),
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
