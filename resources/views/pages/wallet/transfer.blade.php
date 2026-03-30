<?php

use App\Models\User;
use App\Enums\Wallets\WalletType;
use App\Exceptions\Wallets\InsufficientFundsException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Transfer Funds'), Defer] class extends Component {
    #[Url]
    public ?int $recipient_id = null;

    public ?float $amount = null;
    public string $notes = '';

    /**
     * Get all users except the current one for the transfer.
     */
    #[Computed]
    public function users()
    {
        return User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
    }

    /**
     * Perform the transfer.
     */
    public function transfer(): void
    {
        $this->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $sender = Auth::user();
        $recipient = User::find($this->recipient_id);

        try {
            $sender->transfer($this->amount, $recipient, WalletType::General, $this->notes);

            Flux::toast(
                text: __('Successfully transferred :amount to :name', [
                    'amount' => Number::currency($this->amount),
                    'name' => $recipient->name,
                ]),
                variant: 'success',
            );

            $this->redirect(route('wallet.index'), navigate: true);
        } catch (InsufficientFundsException $e) {
            $this->addError('amount', __('Insufficient funds in your wallet.'));
        } catch (\Exception $e) {
            Flux::toast(
                text: __('An error occurred during the transfer.'),
                variant: 'danger',
            );
        }
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-2xl mx-auto space-y-6 animate-pulse">
            <div class="flex items-center space-x-4">
                <div class="h-10 w-10 bg-zinc-200 dark:bg-zinc-700 rounded-lg"></div>
                <div class="h-8 w-48 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            </div>
            <div class="h-96 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
        <flux:button :href="route('wallet.index')" variant="ghost" icon="heroicon-o-arrow-left" inset="left" />
        <flux:heading size="xl">{{ __('Transfer Funds') }}</flux:heading>
    </div>

    <flux:card>
        <form wire:submit="transfer" class="space-y-6">
            <flux:select wire:model="recipient_id" :label="__('Recipient')" placeholder="{{ __('Select a recipient...') }}" searchable>
                @foreach ($this->users as $user)
                    <flux:select.option :value="$user->id">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model="amount"
                type="number"
                step="0.01"
                :label="__('Amount')"
                placeholder="0.00"
                icon="heroicon-o-currency-naira"
            />

            <flux:textarea
                wire:model="notes"
                :label="__('Notes (Optional)')"
                placeholder="{{ __('What is this transfer for?') }}"
                rows="3"
            />

            <div class="flex justify-end space-x-2">
                <flux:button :href="route('wallet.index')" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Send Transfer') }}</flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-dashed">
        <flux:heading size="sm" class="mb-2">{{ __('Transfer Information') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500">
            {{ __('Transfers are processed immediately. Please ensure the recipient and amount are correct before sending.') }}
        </flux:text>
    </flux:card>
</div>
