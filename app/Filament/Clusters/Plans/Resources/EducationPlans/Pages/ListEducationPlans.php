<?php

namespace App\Filament\Clusters\Plans\Resources\EducationPlans\Pages;

use App\Filament\Clusters\Plans\Resources\EducationPlans\EducationPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEducationPlans extends ListRecords
{
    protected static string $resource = EducationPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
