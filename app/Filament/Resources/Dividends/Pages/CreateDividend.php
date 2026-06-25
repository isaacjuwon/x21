<?php

namespace App\Filament\Resources\Dividends\Pages;

use App\Filament\Resources\Dividends\DividendResource;
use Filament\Resources\Pages\CreateRecord;
use App\Actions\Shares\DispatchDividendPayoutJobAction;
use App\Settings\ShareSettings;
use App\Enums\Shares\DividendStatus;

class CreateDividend extends CreateRecord
{
    protected static string $resource = DividendResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $settings = app(ShareSettings::class);
        $data['share_price'] = $settings->price_per_share;
        $data['status'] = DividendStatus::Pending;
        $data['declared_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(DispatchDividendPayoutJobAction::class)->handle($this->record);
    }
}
