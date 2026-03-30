<?php

namespace App\Filament\Resources\Dividends\Pages;

use App\Filament\Resources\Dividends\DividendResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDividend extends EditRecord
{
    protected static string $resource = DividendResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
