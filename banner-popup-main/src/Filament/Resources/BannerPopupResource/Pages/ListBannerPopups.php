<?php

namespace Inudev\BannerPopup\Filament\Resources\BannerPopupResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Inudev\BannerPopup\Filament\Resources\BannerPopupResource;

class ListBannerPopups extends ListRecords
{
    protected static string $resource = BannerPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
