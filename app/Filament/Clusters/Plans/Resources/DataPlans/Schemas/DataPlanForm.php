<?php

namespace App\Filament\Clusters\Plans\Resources\DataPlans\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

                            TextInput::make('api_code')
                                ->required()
                                ->placeholder('Provider API plan code'),
                        ]),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
