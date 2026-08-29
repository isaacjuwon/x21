<?php

namespace App\Filament\Resources\Dividends\Pages;

use App\Actions\Shares\DispatchDividendPayoutJobAction;
use App\Enums\Shares\DividendStatus;
use App\Filament\Resources\Dividends\DividendResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewDividend extends ViewRecord
{
    protected static string $resource = DividendResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dispatchPayouts')
                ->label('Dispatch Payouts')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Dispatch Dividend Payouts')
                ->modalDescription('This will credit all eligible shareholders for this dividend. The action is idempotent — if payouts were already distributed, nothing will happen.')
                ->visible(fn () => $this->record->status === DividendStatus::Pending)
                ->action(function (): void {
                    $dispatched = app(DispatchDividendPayoutJobAction::class)->handle($this->record);

                    if ($dispatched) {
                        Notification::make()
                            ->title('Payouts dispatched')
                            ->body('Dividend payouts have been processed successfully.')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status', 'total_amount']);
                    } else {
                        Notification::make()
                            ->title('Already distributed')
                            ->body('This dividend has already been distributed or is currently being processed.')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}
