<?php

namespace App\Filament\Resources\Loans\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class LoanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Borrower')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('user.name')->label('Name'),
                        TextEntry::make('user.email')->label('Email'),
                    ]),
                ]),

            Section::make('Loan Details')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('principal_amount')
                            ->label('Principal')
                            ->money(fn() => Number::defaultCurrency()),

                        TextEntry::make('outstanding_balance')
                            ->label('Outstanding Balance')
                            ->money(fn() => Number::defaultCurrency()),

                        TextEntry::make('interest_rate')
                            ->label('Interest Rate')
                            ->suffix('%'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('repayment_term_months')
                            ->label('Term')
                            ->suffix(' months'),

                        TextEntry::make('interest_method')
                            ->label('Interest Method')
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('disbursed_at')
                            ->label('Disbursed At')
                            ->dateTime()
                            ->placeholder('Not yet disbursed'),

                        TextEntry::make('eligibility_checked_at')
                            ->label('Eligibility Checked')
                            ->dateTime()
                            ->placeholder('—'),
                    ]),

                    IconEntry::make('eligibility_passed')
                        ->label('Eligibility Passed')
                        ->boolean(),
                ]),

            Section::make('Notes & Rejection')
                ->schema([
                    TextEntry::make('notes')
                        ->label('Notes')
                        ->placeholder('—')
                        ->columnSpanFull(),

                    TextEntry::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ])
                ->collapsed(fn ($record) => ! $record->rejection_reason && ! $record->notes),

            Section::make('Timestamps')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('created_at')->label('Applied At')->dateTime(),
                        TextEntry::make('updated_at')->label('Last Updated')->since(),
                    ]),
                ])
                ->collapsed(),
        ]);
    }
}
