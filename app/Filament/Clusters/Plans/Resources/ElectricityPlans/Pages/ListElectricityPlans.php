<?php

namespace App\Filament\Clusters\Plans\Resources\ElectricityPlans\Pages;

use App\Filament\Clusters\Plans\Resources\ElectricityPlans\ElectricityPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListElectricityPlans extends ListRecords
{
    protected static string $resource = ElectricityPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
