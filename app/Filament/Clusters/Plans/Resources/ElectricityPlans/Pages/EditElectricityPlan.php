<?php

namespace App\Filament\Clusters\Plans\Resources\ElectricityPlans\Pages;

use App\Filament\Clusters\Plans\Resources\ElectricityPlans\ElectricityPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditElectricityPlan extends EditRecord
{
    protected static string $resource = ElectricityPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
