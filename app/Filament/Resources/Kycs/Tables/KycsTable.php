<?php

namespace App\Filament\Resources\Kycs\Tables;

use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Models\Kyc;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KycsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('number')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('method')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (KycStatus $state): string => $state->getColor()),
                TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(KycType::class),
                SelectFilter::make('status')
                    ->options(KycStatus::class),
            ])
            ->recordActions([
                Action::make('verify')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->hidden(fn (Kyc $record): bool => $record->isVerified())
                    ->requiresConfirmation()
                    ->action(function (Kyc $record): void {
                        $record->update([
                            'status' => KycStatus::Verified,
                            'verified_at' => now(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('KYC Verified')
                            ->body("The {$record->type->getLabel()} for {$record->user->name} has been verified.")
                            ->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->hidden(fn (Kyc $record): bool => $record->status === KycStatus::Rejected)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Kyc $record, array $data): void {
                        $record->update([
                            'status' => KycStatus::Rejected,
                            'rejection_reason' => $data['rejection_reason'],
                            'verified_at' => null,
                        ]);

                        Notification::make()
                            ->danger()
                            ->title('KYC Rejected')
                            ->body("The {$record->type->getLabel()} for {$record->user->name} has been rejected.")
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
