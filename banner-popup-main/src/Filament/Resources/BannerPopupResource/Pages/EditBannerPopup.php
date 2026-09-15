<?php

namespace Inudev\BannerPopup\Filament\Resources\BannerPopupResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Inudev\BannerPopup\Filament\Resources\BannerPopupResource;

class EditBannerPopup extends EditRecord
{
    protected static string $resource = BannerPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
