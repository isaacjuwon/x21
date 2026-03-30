<?php

namespace App\Filament\Resources\ShareOrders\Pages;

use App\Filament\Resources\ShareOrders\ShareOrderResource;
use App\Filament\Widgets\ShareOrderStats;
use Filament\Resources\Pages\ListRecords;

class ListShareOrders extends ListRecords
{
    protected static string $resource = ShareOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ShareOrderStats::class,
        ];
    }
}
