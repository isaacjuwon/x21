<?php

namespace App\Filament\Resources\TopupTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TopupTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Transaction Details')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('reference')
                            ->label('Reference')
                            ->copyable()
                            ->fontFamily('mono'),

                        TextEntry::make('type')
                            ->label('Type')
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money('USD'),

                        TextEntry::make('user.name')
                            ->label('User'),

                        TextEntry::make('recipient')
                            ->label('Recipient')
                            ->placeholder('—'),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('api_reference')
                            ->label('API Reference')
                            ->copyable()
                            ->fontFamily('mono')
                            ->placeholder('—'),

                        TextEntry::make('response_message')
                            ->label('Response Message')
                            ->placeholder('—'),
                    ]),
                ]),

            Section::make('Timestamps')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('created_at')->label('Created At')->dateTime(),
                        TextEntry::make('updated_at')->label('Updated At')->since(),
                    ]),
                ])
                ->collapsed(),
        ]);
    }
}
