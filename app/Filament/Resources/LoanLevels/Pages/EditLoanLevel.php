<?php

namespace App\Filament\Resources\LoanLevels\Pages;

use App\Filament\Resources\LoanLevels\LoanLevelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLoanLevel extends EditRecord
{
    protected static string $resource = LoanLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
