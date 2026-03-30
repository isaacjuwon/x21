<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Actions\Loans\ApproveLoanAction;
use App\Actions\Loans\DisburseLoanAction;
use App\Actions\Loans\RejectLoanAction;
use App\Enums\Loans\LoanStatus;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Borrower')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('principal_amount')
                    ->label('Principal')
                    ->money(fn() => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('outstanding_balance')
                    ->label('Balance')
                    ->money(fn() => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('interest_rate')
                    ->label('Rate')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('repayment_term_months')
                    ->label('Term')
                    ->suffix(' mo')
                    ->sortable(),

                TextColumn::make('interest_method')
                    ->label('Method')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(LoanStatus::class)
                    ->multiple(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::HandThumbUp)
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('notes')->label('Notes')->rows(2),
                    ])
                    ->visible(fn ($record) => $record->status === LoanStatus::Active)
                    ->action(function ($record, array $data): void {
                        try {
                            app(ApproveLoanAction::class)->handle($record, auth()->user(), $data['notes'] ?? null);
                            Notification::make()->success()->title('Loan approved')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Approval failed')->body($e->getMessage())->send();
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
                    ->visible(fn ($record) => $record->status === LoanStatus::Approved)
                    ->action(function ($record, array $data): void {
                        app(DisburseLoanAction::class)->handle($record, auth()->user(), $data['notes'] ?? null);
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
                    ->visible(fn ($record) => ! in_array($record->status, [LoanStatus::Disbursed, LoanStatus::Rejected]))
                    ->action(function ($record, array $data): void {
                        app(RejectLoanAction::class)->handle($record, auth()->user(), $data['rejection_reason'], $data['notes'] ?? null);
                        Notification::make()->success()->title('Loan rejected')->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make()->label('Schedule')->icon(Heroicon::TableCells),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('user'));
    }
}
