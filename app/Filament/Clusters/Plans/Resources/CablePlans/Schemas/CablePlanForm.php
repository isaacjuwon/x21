<?php

namespace App\Filament\Clusters\Plans\Resources\CablePlans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class CablePlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required(),
                TextInput::make('type')
                    ->nullable(),
                TextInput::make('api_code')
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->prefix(Number::defaultCurrency()),
                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
