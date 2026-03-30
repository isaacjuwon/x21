<?php

namespace App\Filament\Clusters\Plans\Resources\AirtimePlans\Pages;

use App\Filament\Clusters\Plans\Resources\AirtimePlans\AirtimePlanResource;
use Filament\Resources\Pages\ListRecords;

class ListAirtimePlans extends ListRecords
{
    protected static string $resource = AirtimePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
