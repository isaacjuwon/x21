<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\Loans\InterestMethod;
use App\Enums\Loans\LoanStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Loan Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('user_id')
                            ->label('Borrower')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options(LoanStatus::class)
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('principal_amount')
                            ->label('Principal Amount')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->required(),

                        TextInput::make('interest_rate')
                            ->label('Interest Rate (%)')
                            ->numeric()
                            ->suffix('%')
                            ->required(),

                        TextInput::make('repayment_term_months')
                            ->label('Term (Months)')
                            ->numeric()
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('interest_method')
                            ->label('Interest Method')
                            ->options(InterestMethod::class)
                            ->required(),

                        TextInput::make('outstanding_balance')
                            ->label('Outstanding Balance')
                            ->numeric()
                            ->prefix(Number::defaultCurrency()),
                    ]),
                ]),

            Section::make('Notes & Rejection')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }
}
