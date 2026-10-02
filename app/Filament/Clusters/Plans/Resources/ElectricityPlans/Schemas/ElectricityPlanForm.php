<?php

namespace App\Filament\Clusters\Plans\Resources\ElectricityPlans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ElectricityPlanForm
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
                    ->required(),
                TextInput::make('api_code')
                    ->label('Default API Code (Epins)')
                    ->required()
                    ->placeholder('e.g. PREPAID'),
                TextInput::make('vtpass_code')
                    ->label('VTPass Variation Code')
                    ->nullable()
                    ->placeholder('e.g. prepaid or postpaid')
                    ->helperText('Falls back to API code if empty'),
                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
