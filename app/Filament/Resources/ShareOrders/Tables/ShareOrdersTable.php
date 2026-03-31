<?php

namespace App\Filament\Resources\ShareOrders\Tables;

use App\Actions\Shares\ApproveBuyOrderAction;
use App\Actions\Shares\ApproveSellOrderAction;
use App\Actions\Shares\RejectShareOrderAction;
use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ShareOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('price_per_share')
                    ->label('Price/Share')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ShareOrderStatus::class)
                    ->multiple(),

                SelectFilter::make('type')
                    ->options(ShareOrderType::class),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === ShareOrderStatus::Pending)
                    ->action(function ($record): void {
                        $actor = auth()->user();
                        if ($record->type === ShareOrderType::Buy) {
                            app(ApproveBuyOrderAction::class)->handle($record, $actor);
                        } else {
                            app(ApproveSellOrderAction::class)->handle($record, $actor);
                        }
                        Notification::make()->success()->title('Order approved')->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')->label('Rejection Reason')->required()->rows(2),
                    ])
                    ->visible(fn ($record) => $record->status === ShareOrderStatus::Pending)
                    ->action(function ($record, array $data): void {
                        app(RejectShareOrderAction::class)->handle($record, auth()->user(), $data['rejection_reason']);
                        Notification::make()->success()->title('Order rejected')->send();
                    }),

                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('user'));
    }
}
