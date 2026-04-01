<?php

namespace App\Filament\Resources\TopupTransactions\Pages;

use App\Filament\Resources\TopupTransactions\TopupTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListTopupTransactions extends ListRecords
{
    protected static string $resource = TopupTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
