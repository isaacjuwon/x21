<?php

namespace App\Filament\Resources\LoanLevels\Pages;

use App\Filament\Resources\LoanLevels\LoanLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoanLevels extends ListRecords
{
    protected static string $resource = LoanLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
