<?php

namespace App\Filament\Resources\Dividends\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class DividendForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Declare Dividend')
                ->schema([
                    TextInput::make('total_amount')
                        ->label('Total Amount')
                        ->numeric()
                        ->prefix(Number::defaultCurrency())
                        ->minValue(0.01)
                        ->required(),
                ]),
        ]);
    }
}
