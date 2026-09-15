<?php

namespace App\Filament\Resources\BannerPopupResource\Pages;

use App\Filament\Resources\BannerPopupResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBannerPopups extends ListRecords
{
    protected static string $resource = BannerPopupResource::class;

    /** @return array<array-key, Action> */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
