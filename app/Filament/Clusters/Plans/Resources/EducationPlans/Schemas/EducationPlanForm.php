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
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required(),
                TextInput::make('type')
                    ->nullable(),
                TextInput::make('api_code')
                    ->label('Default API Code (Epins)')
                    ->required()
                    ->placeholder('e.g. WAEC_CHECKER'),
                TextInput::make('vtpass_code')
                    ->label('VTPass Variation Code')
                    ->nullable()
                    ->placeholder('e.g. waecdirect')
                    ->helperText('Falls back to API code if empty'),
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
