<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Actions\Tickets\ReplyToTicketAction;
use App\Actions\Tickets\UpdateTicketStatusAction;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Filament\Resources\Tickets\TicketResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ticket Details')
                ->columns(2)
                ->schema([
                    TextEntry::make('subject')->columnSpanFull(),
                    TextEntry::make('message')->columnSpanFull(),
                    TextEntry::make('status')->badge()->color(fn (TicketStatus $state) => $state->getColor()),
                    TextEntry::make('priority')->badge()->color(fn (TicketPriority $state) => $state->getColor()),
                    TextEntry::make('user.name')->label('Opened By'),
                    TextEntry::make('assignedTo.name')->label('Assigned To')->placeholder('Unassigned'),
                    TextEntry::make('created_at')->label('Opened')->dateTime(),
                    TextEntry::make('resolved_at')->label('Resolved At')->dateTime()->placeholder('Not resolved'),
                ]),

            Section::make('Replies')
                ->schema([
                    RepeatableEntry::make('replies')
                        ->schema([
                            TextEntry::make('user.name')->label('From'),
                            TextEntry::make('message')->columnSpanFull(),
                            TextEntry::make('created_at')->label('At')->dateTime(),
                            TextEntry::make('is_staff')
                                ->label('Type')
                                ->formatStateUsing(fn (bool $state) => $state ? 'Staff' : 'User')
                                ->badge()
                                ->color(fn (bool $state) => $state ? 'violet' : 'zinc'),
                        ])
                        ->columns(3),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('primary')
                ->form([
                    Textarea::make('message')
                        ->label('Message')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    app(ReplyToTicketAction::class)->handle(
                        $this->record,
                        auth()->user(),
                        $data['message'],
                    );
                    $this->refreshFormData([]);
                }),

            Action::make('update_status')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->form([
                    Select::make('status')
                        ->label('Status')
                        ->options(TicketStatus::class)
                        ->required(),
                ])
                ->action(function (array $data) {
                    app(UpdateTicketStatusAction::class)->handle(
                        $this->record,
                        $data['status'] instanceof TicketStatus
                            ? $data['status']
                            : TicketStatus::from($data['status']),
                    );
                    $this->refreshFormData([]);
                }),

            Action::make('assign')
                ->label('Assign')
                ->icon('heroicon-o-user')
                ->color('gray')
                ->form([
                    Select::make('assigned_to')
                        ->label('Assign To')
                        ->options(fn () => User::role(['super_admin', 'admin'])->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['assigned_to' => $data['assigned_to']]);
                }),
        ];
    }
}
