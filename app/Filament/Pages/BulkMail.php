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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BulkMail extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.bulk-mail';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('send_to_all')
                    ->label('Send to all users')
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $state ? $set('users', []) : null),
                Select::make('users')
                    ->label('Select Users')
                    ->multiple()
                    ->searchable()
                    ->options(User::query()->pluck('name', 'id'))
                    ->hidden(fn (callable $get) => $get('send_to_all'))
                    ->required(fn (callable $get) => ! $get('send_to_all')),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Bulk Mail')
                ->submit('send'),
        ];
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $users = $data['send_to_all']
            ? User::all()
            : User::whereIn('id', $data['users'])->get();

        foreach ($users as $user) {
            SendBulkMailJob::dispatch($user, $data['subject'], $data['content']);
        }

        Notification::make()
            ->title('Bulk mail queued')
            ->success()
            ->send();

        $this->form->fill();
    }
}
