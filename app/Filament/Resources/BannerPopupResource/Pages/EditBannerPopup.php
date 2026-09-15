<?php

namespace App\Filament\Resources\BannerPopupResource\Pages;

use App\Filament\Resources\BannerPopupResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBannerPopup extends EditRecord
{
    protected static string $resource = BannerPopupResource::class;

    /** @return array<array-key, Action> */
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
