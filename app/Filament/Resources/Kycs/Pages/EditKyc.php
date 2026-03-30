<?php

namespace App\Filament\Resources\Kycs\Pages;

use App\Filament\Resources\Kycs\KycResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKyc extends EditRecord
{
    protected static string $resource = KycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
