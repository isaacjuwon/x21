<?php

namespace App\Filament\Resources\Dividends\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class DividendInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dividend Details')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('percentage')
                            ->label('Percentage')
                            ->suffix('%'),

                        TextEntry::make('share_price')
                            ->label('Share Price at Declaration')
                            ->money(fn () => Number::defaultCurrency()),

                        TextEntry::make('total_amount')
                            ->label('Total Distributed')
                            ->money(fn () => Number::defaultCurrency())
                            ->placeholder('Pending'),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('declared_at')
                            ->label('Declared At')
                            ->dateTime(),
                    ]),

                    TextEntry::make('payouts_count')
                        ->label('Total Recipients')
                        ->state(fn ($record) => $record->payouts()->count()),
                ]),
        ]);
    }
}
