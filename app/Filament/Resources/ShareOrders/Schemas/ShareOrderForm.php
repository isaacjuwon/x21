<?php

namespace App\Filament\Resources\ShareOrders\Schemas;

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class ShareOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('type')
                            ->label('Type')
                            ->options(ShareOrderType::class)
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required(),

                        TextInput::make('price_per_share')
                            ->label('Price Per Share')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->required(),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix(Number::defaultCurrency())
                            ->required(),
                    ]),

                    Select::make('status')
                        ->label('Status')
                        ->options(ShareOrderStatus::class)
                        ->required(),

                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
