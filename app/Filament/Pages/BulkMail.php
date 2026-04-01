<?php

namespace App\Filament\Pages;

use App\Jobs\SendBulkMailJob;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BulkMail extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('send_to_all')
                    ->label('Send to all users')
                    ->live()
                    ->afterStateUpdated(fn (Set $set, bool $state) => $state ? $set('users', []) : null),
                Select::make('users')
                    ->label('Select Users')
                    ->multiple()
                    ->searchable()
                    ->options(User::query()->pluck('name', 'id'))
                    ->hidden(fn (Get $get): bool => (bool) $get('send_to_all'))
                    ->required(fn (Get $get): bool => ! $get('send_to_all')),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Bulk Mail')
                ->icon('heroicon-o-paper-airplane')
                ->action(function (): void {
                    $data = $this->form->getState();

                    $users = $data['send_to_all']
                        ? User::all()
                        : User::whereIn('id', $data['users'])->get();

                    foreach ($users as $user) {
                        SendBulkMailJob::dispatch($user, $data['subject'], $data['content']);
                    }

                    Notification::make()
                        ->title('Bulk mail queued for '.$users->count().' recipient(s).')
                        ->success()
                        ->send();

                    $this->form->fill();
                }),
        ];
    }
}
