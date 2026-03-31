<?php

namespace App\Filament\Resources\Kycs\Pages;

use App\Enums\Kyc\KycMethod;
use App\Enums\Kyc\KycStatus;
use App\Filament\Resources\Kycs\KycResource;
use App\Models\Kyc;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ViewKyc extends ViewRecord
{
    protected static string $resource = KycResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Verification Details')
                ->columns(2)
                ->schema([
                    TextEntry::make('user.name')->label('User'),
                    TextEntry::make('user.email')->label('Email'),
                    TextEntry::make('type')->badge(),
                    TextEntry::make('method')->badge(),
                    TextEntry::make('number')->label('ID Number')->copyable(),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (KycStatus $state) => $state->getColor()),
                    TextEntry::make('verified_at')->label('Verified At')->dateTime()->placeholder('Not verified'),
                    TextEntry::make('rejection_reason')->label('Rejection Reason')->placeholder('—')->columnSpanFull(),
                ]),

            Section::make('Uploaded Document')
                ->visible(fn (Kyc $record) => $record->method === KycMethod::Manual && $record->file_path)
                ->schema([
                    TextEntry::make('file_path')
                        ->label('Document')
                        ->formatStateUsing(fn (string $state) => basename($state))
                        ->suffixAction(
                            \Filament\Infolists\Components\Actions\Action::make('download')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(fn (Kyc $record) => Storage::url($record->file_path))
                                ->openUrlInNewTab()
                        ),
                    ImageEntry::make('file_path')
                        ->label('Preview')
                        ->disk('public')
                        ->visibility('public')
                        ->visible(fn (Kyc $record) => $record->file_path && str_ends_with(strtolower($record->file_path), '.jpg') || str_ends_with(strtolower($record->file_path ?? ''), '.jpeg') || str_ends_with(strtolower($record->file_path ?? ''), '.png')),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label('Approve & Verify')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Kyc $record) => ! $record->isVerified())
                ->requiresConfirmation()
                ->action(function (Kyc $record): void {
                    $record->update([
                        'status' => KycStatus::Verified,
                        'verified_at' => now(),
                        'rejection_reason' => null,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('KYC Verified')
                        ->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn (Kyc $record) => $record->status !== KycStatus::Rejected)
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Reason for rejection')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (Kyc $record, array $data): void {
                    $record->update([
                        'status' => KycStatus::Rejected,
                        'rejection_reason' => $data['rejection_reason'],
                        'verified_at' => null,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->danger()
                        ->title('KYC Rejected')
                        ->send();
                }),
        ];
    }
}
