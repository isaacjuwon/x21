<?php

namespace App\Filament\Resources\TopupTransactions\Pages;

use App\Filament\Resources\TopupTransactions\TopupTransactionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTopupTransaction extends ViewRecord
{
    protected static string $resource = TopupTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
