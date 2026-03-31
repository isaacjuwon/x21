<?php

namespace App\Filament\Resources\Dividends\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DividendForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Declare Dividend')
                ->schema([
                    TextInput::make('percentage')
                        ->label('Dividend Percentage')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0.01)
                        ->required(),
                ]),
        ]);
    }
}
