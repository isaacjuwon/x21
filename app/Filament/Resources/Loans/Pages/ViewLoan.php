<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Actions\Loans\ApproveLoanAction;
use App\Actions\Loans\CalculateLoanPayoffAction;
use App\Actions\Loans\DisburseLoanAction;
use App\Actions\Loans\PayoffLoanAction;
use App\Actions\Loans\RejectLoanAction;
use App\Enums\Loans\LoanStatus;
use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

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
                    $lock = Cache::lock("loan-action:{$this->record->id}:approve", 30);

                    if (! $lock->get()) {
                        Notification::make()->warning()->title('Already processing')->body('This loan is currently being approved. Please wait.')->send();

                        return;
                    }

                    try {
                        $this->record->refresh();

                        if ($this->record->status !== LoanStatus::Active) {
                            Notification::make()->warning()->title('Already actioned')->body('This loan has already been approved or its status has changed.')->send();

                            return;
                        }

                        app(ApproveLoanAction::class)->handle($this->record, auth()->user(), $data['notes'] ?? null);
                        $this->refreshFormData(['status', 'notes']);
                        Notification::make()->success()->title('Loan approved')->send();
                    } finally {
                        $lock->release();
                    }
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
                    $lock = Cache::lock("loan-action:{$this->record->id}:disburse", 30);

                    if (! $lock->get()) {
                        Notification::make()->warning()->title('Already processing')->body('This loan is currently being disbursed. Please wait.')->send();

                        return;
                    }

                    try {
                        $this->record->refresh();

                        if ($this->record->status !== LoanStatus::Approved) {
                            Notification::make()->warning()->title('Already actioned')->body('This loan has already been disbursed or its status has changed.')->send();

                            return;
                        }

                        app(DisburseLoanAction::class)->handle($this->record, auth()->user(), $data['notes'] ?? null);
                        $this->refreshFormData(['status', 'notes', 'disbursed_at']);
                        Notification::make()->success()->title('Loan disbursed')->send();
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
                    Textarea::make('notes')->label('Notes')->rows(2),
                ])
                ->visible(fn () => ! in_array($this->record->status, [LoanStatus::Disbursed, LoanStatus::Rejected]))
                ->action(function (array $data): void {
                    $lock = Cache::lock("loan-action:{$this->record->id}:reject", 30);

                    if (! $lock->get()) {
                        Notification::make()->warning()->title('Already processing')->body('This loan is currently being rejected. Please wait.')->send();

                        return;
                    }

                    try {
                        $this->record->refresh();

                        if (in_array($this->record->status, [LoanStatus::Disbursed, LoanStatus::Rejected])) {
                            Notification::make()->warning()->title('Already actioned')->body('This loan has already been disbursed or rejected.')->send();

                            return;
                        }

                        app(RejectLoanAction::class)->handle($this->record, auth()->user(), $data['rejection_reason'], $data['notes'] ?? null);
                        $this->refreshFormData(['status', 'rejection_reason', 'notes']);
                        Notification::make()->success()->title('Loan rejected')->send();
                    } finally {
                        $lock->release();
                    }
                }),

            Action::make('payoff')
                ->label('Payoff Loan')
                ->icon(Heroicon::CheckBadge)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Payoff Loan Early')
                ->modalDescription(function () {
                    $quote = app(CalculateLoanPayoffAction::class)->handle($this->record);
                    $currency = Number::defaultCurrency();

                    return "Review payoff quote:\n".
                        'Remaining Principal: '.Number::currency($quote['remaining_principal'], $currency)."\n".
                        'Accrued Interest: '.Number::currency($quote['accrued_interest'], $currency)."\n".
                        'Prepayment Penalty: '.Number::currency($quote['prepayment_penalty'], $currency)."\n".
                        'Total Payoff Amount: '.Number::currency($quote['total_payoff_amount'], $currency);
                })
                ->visible(fn () => $this->record->status === LoanStatus::Disbursed)
                ->action(function (): void {
                    $lock = Cache::lock("loan-action:{$this->record->id}:payoff", 30);

                    if (! $lock->get()) {
                        Notification::make()->warning()->title('Already processing')->body('A payoff for this loan is currently being processed.')->send();

                        return;
                    }

                    try {
                        $this->record->refresh();

                        if ($this->record->status !== LoanStatus::Disbursed) {
                            Notification::make()->warning()->title('Already actioned')->body('This loan has already been paid off or its status has changed.')->send();

                            return;
                        }

                        app(PayoffLoanAction::class)->handle($this->record, auth()->user());
                        $this->refreshFormData(['status', 'outstanding_balance']);
                        Notification::make()->success()->title('Loan paid off successfully')->send();
                    } catch (\Exception $e) {
                        Notification::make()->danger()->title('Payoff failed')->body($e->getMessage())->send();
                    } finally {
                        $lock->release();
                    }
                }),

            EditAction::make(),
        ];
    }
}
