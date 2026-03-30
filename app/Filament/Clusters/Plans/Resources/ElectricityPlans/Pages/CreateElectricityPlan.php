<?php

namespace App\Filament\Clusters\Plans\Resources\ElectricityPlans\Pages;

use App\Filament\Clusters\Plans\Resources\ElectricityPlans\ElectricityPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectricityPlan extends CreateRecord
{
    protected static string $resource = ElectricityPlanResource::class;
}
