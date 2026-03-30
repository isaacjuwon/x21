<?php

namespace App\Filament\Clusters\Plans\Resources\CablePlans\Pages;

use App\Filament\Clusters\Plans\Resources\CablePlans\CablePlanResource;
use Filament\Resources\Pages\EditRecord;

class EditCablePlan extends EditRecord
{
    protected static string $resource = CablePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
