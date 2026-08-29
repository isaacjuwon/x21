<?php

namespace App\Filament\Resources\ShareOrders\Pages;

use App\Actions\Shares\ApproveBuyOrderAction;
use App\Actions\Shares\ApproveSellOrderAction;
use App\Actions\Shares\RejectShareOrderAction;
use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Filament\Resources\ShareOrders\ShareOrderResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class ViewShareOrder extends ViewRecord
{
    protected static string $resource = ShareOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === ShareOrderStatus::Pending)
                ->action(function (): void {
                    $lock = Cache::lock("share-order-action:{$this->record->id}:approve", 30);

                    if (! $lock->get()) {
                        Notification::make()->warning()->title('Already processing')->body('This order is currently being approved. Please wait.')->send();

                        return;
                    }

                    try {
                        $this->record->refresh();

                        if ($this->record->status !== ShareOrderStatus::Pending) {
                            Notification::make()->warning()->title('Already actioned')->body('This order has already been approved or its status has changed.')->send();

                            return;
                        }

                        $actor = auth()->user();
                        if ($this->record->type === ShareOrderType::Buy) {
                            app(ApproveBuyOrderAction::class)->handle($this->record, $actor);
                        } else {
                            app(ApproveSellOrderAction::class)->handle($this->record, $actor);
                        }

                        $this->refreshFormData(['status']);
                        Notification::make()->success()->title('Order approved')->send();
                    } finally {
                        $lock->release();
                    }
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')->label('Rejection Reason')->required()->rows(2),
                ])
                ->visible(fn () => $this->record->status === ShareOrderStatus::Pending)
                ->action(function (array $data): void {
                    $lock = Cache::lock("share-order-action:{$this->record->id}:reject", 30);

                    if (! $lock->get()) {
                        Notification::make()->warning()->title('Already processing')->body('This order is currently being rejected. Please wait.')->send();

                        return;
                    }

                    try {
                        $this->record->refresh();

                        if ($this->record->status !== ShareOrderStatus::Pending) {
                            Notification::make()->warning()->title('Already actioned')->body('This order has already been actioned or its status has changed.')->send();

                            return;
                        }

                        app(RejectShareOrderAction::class)->handle($this->record, auth()->user(), $data['rejection_reason']);
                        $this->refreshFormData(['status', 'rejection_reason']);
                        Notification::make()->success()->title('Order rejected')->send();
                    } finally {
                        $lock->release();
                    }
                }),
        ];
    }
}
