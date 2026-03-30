<?php

namespace App\Filament\Resources\LoanLevels\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class LoanLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Level Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Level Name')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->inline(false)
                            ->default(true),
                    ]),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Loan Parameters')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('min_amount')
                            ->label('Minimum Amount')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->minValue(0)
                            ->required(),

                        TextInput::make('max_amount')
                            ->label('Maximum Amount')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->minValue(1)
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('interest_rate')
                            ->label('Interest Rate (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->required(),

                        TextInput::make('max_term_months')
                            ->label('Max Term (Months)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
                ]),
        ]);
    }
}
