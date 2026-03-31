<?php

namespace App\Filament\Clusters\Plans\Resources\CablePlans\Pages;

use App\Filament\Clusters\Plans\Resources\CablePlans\CablePlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCablePlan extends EditRecord
{
    protected static string $resource = CablePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
