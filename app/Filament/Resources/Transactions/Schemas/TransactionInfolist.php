<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
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

                        TextEntry::make('wallet.user.name')
                            ->label('User'),

                        TextEntry::make('wallet.type')
                            ->label('Wallet Type')
                            ->badge(),
                    ]),

                    TextEntry::make('notes')
                        ->label('Notes')
                        ->placeholder('—')
                        ->columnSpanFull(),

                    TextEntry::make('failure_reason')
                        ->label('Failure Reason')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            Section::make('Refund')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('refundFor.reference')
                            ->label('Refund For Reference')
                            ->placeholder('—')
                            ->copyable()
                            ->fontFamily('mono'),

                        TextEntry::make('refund.reference')
                            ->label('Refund Transaction')
                            ->placeholder('—')
                            ->copyable()
                            ->fontFamily('mono'),
                    ]),
                ])
                ->collapsed(fn ($record) => ! $record->refund_for_id && ! $record->refund),

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
