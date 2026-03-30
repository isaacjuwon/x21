<?php

namespace App\Filament\Clusters\Plans\Resources\EducationPlans\Pages;

use App\Filament\Clusters\Plans\Resources\EducationPlans\EducationPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEducationPlan extends CreateRecord
{
    protected static string $resource = EducationPlanResource::class;
}
