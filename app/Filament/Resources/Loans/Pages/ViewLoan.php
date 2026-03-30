<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Actions\Loans\ApproveLoanAction;
use App\Actions\Loans\DisburseLoanAction;
use App\Actions\Loans\RejectLoanAction;
use App\Enums\Loans\LoanStatus;
use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon(Heroicon::HandThumbUp)
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('notes')->label('Notes')->rows(2),
                ])
                ->visible(fn () => $this->record->status === LoanStatus::Active)
                ->action(function (array $data): void {
                    app(ApproveLoanAction::class)->handle($this->record, auth()->user(), $data['notes'] ?? null);
                    $this->refreshFormData(['status', 'notes']);
                    Notification::make()->success()->title('Loan approved')->send();
                }),

            Action::make('disburse')
                ->label('Disburse')
                ->icon(Heroicon::Banknotes)
                ->color('primary')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('notes')->label('Notes')->rows(2),
                ])
                ->visible(fn () => $this->record->status === LoanStatus::Approved)
                ->action(function (array $data): void {
                    app(DisburseLoanAction::class)->handle($this->record, auth()->user(), $data['notes'] ?? null);
                    $this->refreshFormData(['status', 'notes', 'disbursed_at']);
                    Notification::make()->success()->title('Loan disbursed')->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')->label('Rejection Reason')->required()->rows(2),
                    Textarea::make('notes')->label('Notes')->rows(2),
                ])
                ->visible(fn () => ! in_array($this->record->status, [LoanStatus::Disbursed, LoanStatus::Rejected]))
                ->action(function (array $data): void {
                    app(RejectLoanAction::class)->handle($this->record, auth()->user(), $data['rejection_reason'], $data['notes'] ?? null);
                    $this->refreshFormData(['status', 'rejection_reason', 'notes']);
                    Notification::make()->success()->title('Loan rejected')->send();
                }),

            EditAction::make(),
        ];
    }
}
