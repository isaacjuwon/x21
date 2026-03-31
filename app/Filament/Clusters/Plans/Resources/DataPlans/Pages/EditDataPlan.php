<?php

namespace App\Filament\Clusters\Plans\Resources\DataPlans\Pages;

use App\Filament\Clusters\Plans\Resources\DataPlans\DataPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDataPlan extends EditRecord
{
    protected static string $resource = DataPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
