<?php

namespace App\Filament\Clusters\Plans\Resources\AirtimePlans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AirtimePlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required(),
                TextInput::make('type')
                    ->nullable(),
                TextInput::make('api_code')
                    ->required(),
                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
