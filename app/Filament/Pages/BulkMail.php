<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BulkMail extends Page
{
    protected string $view = 'filament.pages.bulk-mail';

    //use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 20;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'send_to_all' => false,
            'users' => [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('send_to_all')
                    ->label('Send to all users')
                    ->live()
                    ->default(false)
                    ->afterStateUpdated(fn (Set $set, bool $state) => $state ? $set('users', []) : null),
                Select::make('users')
                    ->label('Select Users')
                    ->multiple()
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn (string $search) => User::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                    )
                    ->getOptionLabelsUsing(
                        fn (array $values) => User::whereIn('id', $values)->pluck('name', 'id')
                    )
                    ->hidden(fn (Get $get): bool => (bool) $get('send_to_all'))
                    ->required(fn (Get $get): bool => ! (bool) $get('send_to_all')),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $users = (bool) ($data['send_to_all'] ?? false)
            ? User::all()
            : User::whereIn('id', $data['users'] ?? [])->get();

        if ($users->isEmpty()) {
            Notification::make()
                ->title('No recipients selected.')
                ->warning()
                ->send();
            return;
        }

        foreach ($users as $user) {
            SendBulkMailJob::dispatch($user, $data['subject'], $data['content']);
        }

        Notification::make()
            ->title('Bulk mail queued for ' . $users->count() . ' recipient(s).')
            ->success()
            ->send();

        $this->form->fill([
            'send_to_all' => false,
            'users' => [],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Bulk Mail')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalHeading('Confirm Bulk Send')
                ->modalDescription('This will queue emails for all selected recipients. Are you sure?')
                ->action(fn () => $this->send()),
        ];
    }
}
