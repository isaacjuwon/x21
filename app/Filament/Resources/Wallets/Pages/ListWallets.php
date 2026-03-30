<?php

namespace App\Filament\Resources\Wallets\Pages;

use App\Filament\Resources\Wallets\Tables\WalletsTable;
use App\Filament\Resources\Wallets\WalletResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    public function table(Table $table): Table
    {
        return WalletsTable::configure($table);
    }
}
