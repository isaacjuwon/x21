<?php

namespace Inudev\BannerPopup\Filament\Resources\BannerPopupResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Inudev\BannerPopup\Filament\Resources\BannerPopupResource;

class CreateBannerPopup extends CreateRecord
{
    protected static string $resource = BannerPopupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
