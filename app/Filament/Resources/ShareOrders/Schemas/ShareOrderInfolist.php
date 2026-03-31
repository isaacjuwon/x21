<?php

namespace App\Filament\Resources\ShareOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class ShareOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('User')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('user.name')->label('Name'),
                        TextEntry::make('user.email')->label('Email'),
                    ]),
                ]),

            Section::make('Order Details')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('type')->label('Type')->badge(),
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('quantity')->label('Quantity')->numeric(),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('price_per_share')->label('Price / Share')->money(fn () => Number::defaultCurrency()),
                        TextEntry::make('total_amount')->label('Total Amount')->money(fn () => Number::defaultCurrency()),
                        TextEntry::make('created_at')->label('Placed At')->dateTime(),
                    ]),
                ]),

            Section::make('Rejection')
                ->schema([
                    TextEntry::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ])
                ->collapsed(fn ($record) => ! $record->rejection_reason),
        ]);
    }
}
