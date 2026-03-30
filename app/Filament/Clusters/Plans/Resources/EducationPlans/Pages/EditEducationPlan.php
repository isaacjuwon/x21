<?php

namespace App\Filament\Clusters\Plans\Resources\EducationPlans\Pages;

use App\Filament\Clusters\Plans\Resources\EducationPlans\EducationPlanResource;
use Filament\Resources\Pages\EditRecord;

class EditEducationPlan extends EditRecord
{
    protected static string $resource = EducationPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
