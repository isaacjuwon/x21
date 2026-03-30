<?php

namespace App\Filament\Clusters\Plans\Resources\AirtimePlans\Pages;

use App\Filament\Clusters\Plans\Resources\AirtimePlans\AirtimePlanResource;
use Filament\Resources\Pages\EditRecord;

class EditAirtimePlan extends EditRecord
{
    protected static string $resource = AirtimePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
