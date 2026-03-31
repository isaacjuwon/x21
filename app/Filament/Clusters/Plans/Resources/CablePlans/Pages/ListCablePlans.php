<?php

namespace App\Filament\Clusters\Plans\Resources\CablePlans\Pages;

use App\Filament\Clusters\Plans\Resources\CablePlans\CablePlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCablePlans extends ListRecords
{
    protected static string $resource = CablePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
