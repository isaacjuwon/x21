<?php

namespace App\Filament\Clusters\Plans\Resources\DataPlans\Pages;

use App\Filament\Clusters\Plans\Resources\DataPlans\DataPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataPlans extends ListRecords
{
    protected static string $resource = DataPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
