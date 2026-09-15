<?php

namespace App\Filament\Resources\BannerPopupResource\Pages;

use App\Filament\Resources\BannerPopupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBannerPopup extends CreateRecord
{
    protected static string $resource = BannerPopupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
