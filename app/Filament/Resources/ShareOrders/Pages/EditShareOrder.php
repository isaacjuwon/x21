<?php

namespace App\Filament\Resources\ShareOrders\Pages;

use App\Filament\Resources\ShareOrders\ShareOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShareOrder extends EditRecord
{
    protected static string $resource = ShareOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
