<?php

namespace App\Filament\Clusters\Plans\Resources\DataPlans\Pages;

use App\Filament\Clusters\Plans\Resources\DataPlans\DataPlanResource;
use Filament\Resources\Pages\EditRecord;

class EditDataPlan extends EditRecord
{
    protected static string $resource = DataPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
