<?php

use App\Models\Wallet;
use App\Enums\Wallets\WalletType;
use App\Exceptions\Wallets\InsufficientFundsException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Withdraw Funds'), Defer] class extends Component {
    public ?float $amount = null;
    public string $notes = '';

    /**
     * Get the current user's wallet.
     */
    #[Computed]
    public function wallet(): Wallet
    {
        return Auth::user()->getWallet(WalletType::General);
    }

    /**
     * Perform the withdrawal.
     */
    public function withdraw(): void
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            Auth::user()->withdraw($this->amount, WalletType::General, $this->notes ?: __('Manual withdrawal'));

            Flux::toast(
                text: __('Successfully withdrawn :amount from your wallet', [
                    'amount' => Number::currency($this->amount),
                ]),
                variant: 'success',
            );

            $this->redirect(route('wallet.index'), navigate: true);
        } catch (InsufficientFundsException $e) {
            $this->addError('amount', __('Insufficient funds in your wallet.'));
        } catch (\Exception $e) {
            Flux::toast(
                text: __('An error occurred during the withdrawal.'),
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
        <flux:heading size="xl">{{ __('Withdraw Funds') }}</flux:heading>
    </div>

    <flux:card>
        <flux:callout icon="clock" variant="secondary" class="mb-6">
            <flux:callout.heading>Processing Time</flux:callout.heading>
            <flux:callout.text>Withdrawals are subject to verification and may take up to 24 hours to process. Please ensure your withdrawal details are correct.</flux:callout.text>
        </flux:callout>

        <div class="mb-6 flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
            <div class="space-y-1">
                <flux:heading size="sm" class="text-zinc-500">{{ __('Available Balance') }}</flux:heading>
                <flux:text size="xl" weight="semibold">
                    {{ Number::currency($this->wallet->available_balance) }}
                </flux:text>
            </div>
            <flux:icon icon="heroicon-o-banknotes" class="h-10 w-10 text-zinc-300" />
        </div>

        <form wire:submit="withdraw" class="space-y-6">
            <flux:input
                wire:model="amount"
                type="number"
                step="0.01"
                :label="__('Amount to Withdraw')"
                placeholder="0.00"
                icon="heroicon-o-currency-naira"
                required
            />

            <flux:textarea
                wire:model="notes"
                :label="__('Notes (Optional)')"
                placeholder="{{ __('Where should we send this?') }}"
                rows="3"
            />

            <div class="flex justify-end space-x-2">
                <flux:button :href="route('wallet.index')" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Confirm Withdrawal') }}</flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-dashed">
        <flux:heading size="sm" class="mb-2">{{ __('Withdrawal Information') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500">
            {{ __('Withdrawals are subject to verification. Please allow up to 24 hours for processing.') }}
        </flux:text>
    </flux:card>
</div>
