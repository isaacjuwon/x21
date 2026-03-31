<?php

use App\Actions\Wallets\InitializeWalletFundingAction;
use App\Actions\Wallets\VerifyWalletFundingAction;
use App\Enums\Wallets\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Fund Wallet')] class extends Component {

    public ?float $amount = null;

    /** Paystack callback injects this via ?reference= query param */
    #[Url]
    public ?string $reference = null;

    public function mount(): void
    {
        // If we're back from Paystack callback, run verification immediately
        if ($this->reference) {
            $this->verifyPayment();
        }
    }

    /**
     * Step 1 — User submits amount, we initialize with Paystack and redirect.
     */
    public function fund(InitializeWalletFundingAction $action): mixed
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        try {
            $authorizationUrl = $action->handle(Auth::user(), (float) $this->amount);

            // Hard redirect away to Paystack's hosted payment page
            return redirect()->away($authorizationUrl);
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', heading: 'Initialization Failed', text: $e->getMessage());
        }

        return null;
    }

    /**
     * Step 2 — User returns from Paystack. Verify via API (webhook may have already credited).
     */
    public function verifyPayment(): void
    {
        if (! $this->reference) {
            return;
        }

        try {
            $transaction = app(VerifyWalletFundingAction::class)->handle($this->reference);

            if ($transaction->status === TransactionStatus::Completed) {
                Flux::toast(
                    variant: 'success',
                    heading: 'Payment Successful',
                    text: 'Your wallet has been credited with '.Number::currency($transaction->amount).'.',
                );

                $this->redirect(route('wallet.index'), navigate: true);

                return;
            }

            Flux::toast(
                variant: 'danger',
                heading: 'Payment Unsuccessful',
                text: 'The payment could not be verified. Please try again or contact support.',
            );
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', heading: 'Verification Failed', text: $e->getMessage());
        }

        $this->reference = null;
    }

    #[Computed]
    public function isVerifying(): bool
    {
        return $this->reference !== null;
    }
}; ?>

<div class="max-w-xl mx-auto space-y-6">

    @if ($this->isVerifying)
        {{-- Callback state: show spinner while verifyPayment() runs in mount() --}}
        <div class="flex flex-col items-center justify-center py-16 space-y-4">
            <flux:icon.arrow-path class="size-10 text-zinc-400 animate-spin" />
            <flux:heading size="lg">Verifying your payment…</flux:heading>
            <flux:text class="text-zinc-500">Please wait while we confirm your transaction with Paystack.</flux:text>
        </div>
    @else
        <div class="space-y-1">
            <flux:heading size="xl">{{ __('Fund Wallet') }}</flux:heading>
            <flux:subheading>{{ __('Add money to your wallet securely via Paystack.') }}</flux:subheading>
        </div>

        <flux:card class="space-y-6">
            <form wire:submit="fund" class="space-y-6">
                <flux:field>
                    <flux:label>{{ __('Amount') }}</flux:label>
                    <flux:input
                        wire:model="amount"
                        type="number"
                        step="0.01"
                        min="100"
                        placeholder="0.00"
                        icon="banknotes"
                    />
                    <flux:error name="amount" />
                </flux:field>

                <flux:callout icon="information-circle" color="blue">
                    <flux:callout.text>
                        {{ __('You will be redirected to Paystack\'s secure payment page. Your wallet will be credited automatically once payment is confirmed.') }}
                    </flux:callout.text>
                </flux:callout>

                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="fund">{{ __('Continue to Payment') }}</span>
                    <span wire:loading wire:target="fund">{{ __('Redirecting…') }}</span>
                </flux:button>
            </form>
        </flux:card>

        <flux:button :href="route('wallet.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Back to Wallet') }}
        </flux:button>
    @endif

</div>
