<?php

namespace App\Filament\Resources\Dividends\Pages;

use App\Actions\Shares\DispatchDividendPayoutJobAction;
use App\Enums\Shares\DividendStatus;
use App\Filament\Resources\Dividends\DividendResource;
use App\Settings\ShareSettings;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

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
        $dispatched = app(DispatchDividendPayoutJobAction::class)->handle($this->record);

        if (! $dispatched) {
            Notification::make()
                ->title('Payout already processed')
                ->body('This dividend has already been distributed. No payout job was dispatched.')
                ->warning()
                ->send();
        }
    }
}
