<?php

namespace App\Filament\Pages;

use App\Jobs\SendBulkMailJob;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Spatie\Permission\Models\Role;
use UnitEnum;

class BulkMailPage extends Page
{
    protected string $view = 'filament.pages.bulk-mail-page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static string|UnitEnum|null $navigationGroup = 'Users & Access';

    protected static ?int $navigationSort = 99;

    protected static ?string $navigationLabel = 'Bulk Mail';

    protected static ?string $title = 'Send Bulk Mail';

    public $subject = '';

    public $message = '';

    public $recipients = 'all';

    /** @var array<string>|null */
    public $selectedRoles = [];

    /** @var array<int>|null */
    public $selectedUsers = [];

    public $singleUser = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Recipients')
                    ->description('Choose who will receive this email.')
                    ->schema([
                        Select::make('recipients')
                            ->label('Send To')
                            ->options([
                                'all' => 'All Users',
                                'verified' => 'Verified Users Only',
                                'shares' => 'Users With Shares',
                                'roles' => 'Specific Roles',
                                'select' => 'Select Users',
                                'one' => 'One User',
                            ])
                            ->default('all')
                            ->live()
                            ->required(),

                        CheckboxList::make('selectedRoles')
                            ->label('Select Roles')
                            ->options(fn () => Role::pluck('name', 'name')->toArray())
                            ->visible(fn ($get) => $get('recipients') === 'roles')
                            ->required(fn ($get) => $get('recipients') === 'roles')
                            ->columns(3),

                        Select::make('selectedUsers')
                            ->label('Select Users')
                            ->multiple()
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn (string $search) => User::where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->limit(20)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getOptionLabelsUsing(
                                fn (array $values) => User::whereIn('id', $values)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->visible(fn ($get) => $get('recipients') === 'select')
                            ->required(fn ($get) => $get('recipients') === 'select'),

                        Select::make('singleUser')
                            ->label('User')
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn (string $search) => User::where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->limit(20)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getOptionLabelUsing(
                                fn ($value) => User::find($value)?->name
                            )
                            ->visible(fn ($get) => $get('recipients') === 'one')
                            ->required(fn ($get) => $get('recipients') === 'one'),
                    ]),

                Section::make('Compose Email')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Subject')
                            ->placeholder('Enter email subject…')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('message')
                            ->label('Message')
                            ->placeholder('Write your message here…')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'h2',
                                'h3',
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Email')
                ->icon(Heroicon::PaperAirplane)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Send Bulk Mail?')
                ->modalDescription('This will queue an email to all selected recipients. Are you sure?')
                ->modalSubmitActionLabel('Yes, Send Now')
                ->action(function (): void {
                    $data = $this->form->getState();

                    $recipientTarget = $data['recipients'] ?? 'all';
                    $roles = $data['selectedRoles'] ?? [];
                    $userIds = $data['selectedUsers'] ?? [];
                    $singleUserId = $data['singleUser'] ?? null;
                    $subject = $data['subject'] ?? '';
                    $body = strip_tags($data['message'] ?? '');

                    $query = User::query();

                    if ($recipientTarget === 'verified') {
                        $query->whereNotNull('email_verified_at');
                    } elseif ($recipientTarget === 'shares') {
                        $query->whereHas('shareHoldings', fn ($q) => $q->where('quantity', '>', 0));
                    } elseif ($recipientTarget === 'roles' && ! empty($roles)) {
                        $query->whereHas('roles', fn ($q) => $q->whereIn('name', $roles));
                    } elseif ($recipientTarget === 'select' && ! empty($userIds)) {
                        $query->whereIn('id', $userIds);
                    } elseif ($recipientTarget === 'one' && $singleUserId) {
                        $query->where('id', $singleUserId);
                    }

                    $recipients = $query->get();

                    foreach ($recipients as $user) {
                        SendBulkMailJob::dispatch($user, $subject, $body);
                    }

                    $this->form->fill();

                    Notification::make()
                        ->success()
                        ->title('Bulk mail queued!')
                        ->body("Email queued for {$recipients->count()} recipient(s).")
                        ->send();
                }),
        ];
    }
}
