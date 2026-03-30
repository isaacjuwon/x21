<?php

namespace App\Filament\Clusters\Plans\Resources\EducationPlans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class EducationPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('api_code')
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->prefix(Number::defaultCurrency())
                    ->required(),
                TextInput::make('duration')
                    ->required(),
                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
