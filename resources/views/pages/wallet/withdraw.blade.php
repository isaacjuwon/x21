<?php

use App\Actions\Wallets\WithdrawWalletAction;
use App\Enums\Wallets\WalletType;
use App\Exceptions\Wallets\InsufficientFundsException;
use App\Integrations\Paystack\PaystackConnector;
use App\Models\Wallet;
use App\Settings\WalletSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Withdraw Funds')] class extends Component {
    public ?float $amount = null;
    public string $account_number = '';
    public string $bank_code = '';
    public string $bank_name = '';

    // Resolved from Paystack
    public ?string $resolved_account_name = null;
    public bool $account_verified = false;

    // Charges preview
    public ?array $charges = null;

    #[Computed]
    public function wallet(): Wallet
    {
        return Auth::user()->getWallet(WalletType::General);
    }

    #[Computed]
    public function banks(): \Illuminate\Support\Collection
    {
        try {
            return app(PaystackConnector::class)->bank()->list();
        } catch (\Exception) {
            return collect();
        }
    }

    #[Computed]
    public function settings(): WalletSettings
    {
        return app(WalletSettings::class);
    }

    public function updatedAmount(): void
    {
        $this->charges = null;
        if ($this->amount > 0) {
            $this->charges = app(WithdrawWalletAction::class)->calculateCharges((float) $this->amount);
        }
    }

    public function updatedBankCode(): void
    {
        $this->resolved_account_name = null;
        $this->account_verified = false;
        $bank = $this->banks->firstWhere('code', $this->bank_code);
        $this->bank_name = $bank?->name ?? '';
    }

    public function verifyAccount(): void
    {
        $this->validate([
            'account_number' => ['required', 'digits:10'],
            'bank_code' => ['required', 'string'],
        ]);

        try {
            $account = app(PaystackConnector::class)->bank()->resolve($this->account_number, $this->bank_code);
            $this->resolved_account_name = $account->accountName;
            $this->account_verified = true;
        } catch (\Exception) {
            $this->resolved_account_name = null;
            $this->account_verified = false;
            $this->addError('account_number', 'Could not verify account. Please check the account number and bank.');
        }
    }

    public function withdraw(WithdrawWalletAction $action): void
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'account_number' => ['required', 'digits:10'],
            'bank_code' => ['required', 'string'],
            'bank_name' => ['required', 'string'],
        ]);

        if (! $this->account_verified) {
            $this->addError('account_number', 'Please verify your account number first.');
            return;
        }

        try {
            $action->handle(
                user: Auth::user(),
                amount: (float) $this->amount,
                accountNumber: $this->account_number,
                bankCode: $this->bank_code,
                bankName: $this->bank_name,
            );

            Flux::toast(
                text: __('Withdrawal of :amount initiated successfully.', ['amount' => Number::currency((float) $this->amount)]),
                variant: 'success',
            );

            $this->redirect(route('wallet.index'), navigate: true);
        } catch (InsufficientFundsException) {
            $this->addError('amount', __('Insufficient funds. Your balance does not cover the amount plus charges.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
        } catch (\Exception $e) {
            Flux::toast(text: __('Withdrawal failed: :msg', ['msg' => $e->getMessage()]), variant: 'danger');
        }
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="max-w-2xl mx-auto space-y-6 animate-pulse">
            <div class="h-8 w-48 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            <div class="h-96 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <flux:button :href="route('wallet.index')" variant="ghost" icon="arrow-left" inset="left" wire:navigate />
        <flux:heading size="xl">{{ __('Withdraw Funds') }}</flux:heading>
    </div>

    {{-- Balance card --}}
    <flux:card class="flex items-center justify-between p-4">
        <div>
            <flux:text class="text-zinc-500 text-sm">{{ __('Available Balance') }}</flux:text>
            <flux:heading size="lg">{{ Number::currency($this->wallet->available_balance) }}</flux:heading>
        </div>
        <flux:icon name="banknotes" class="size-10 text-zinc-300 dark:text-zinc-600" />
    </flux:card>

    <flux:card class="space-y-6">
        <form wire:submit="withdraw" class="space-y-5">

            {{-- Amount --}}
            <flux:field>
                <flux:label>{{ __('Amount') }}</flux:label>
                <flux:input
                    wire:model.live.debounce.400ms="amount"
                    type="number"
                    step="0.01"
                    min="{{ $this->settings->min_withdrawal }}"
                    placeholder="0.00"
                    icon="banknotes"
                />
                <flux:description>Minimum: {{ Number::currency($this->settings->min_withdrawal) }}</flux:description>
                <flux:error name="amount" />
            </flux:field>

            {{-- Charges breakdown --}}
            @if($charges)
                <div class="rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">{{ __('Withdrawal Amount') }}</span>
                        <span>{{ Number::currency($charges['amount']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">{{ __('Processing Fee') }}</span>
                        <span>{{ Number::currency($charges['fee']) }}</span>
                    </div>
                    @if($charges['stamp_duty'] > 0)
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Stamp Duty') }}</span>
                            <span>{{ Number::currency($charges['stamp_duty']) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold border-t border-zinc-200 dark:border-zinc-700 pt-2">
                        <span>{{ __('Total Debit') }}</span>
                        <span>{{ Number::currency($charges['total']) }}</span>
                    </div>
                </div>
            @endif

            {{-- Bank selection --}}
            <flux:field>
                <flux:label>{{ __('Bank') }}</flux:label>
                <flux:select wire:model.live="bank_code" placeholder="{{ __('Select your bank...') }}" searchable>
                    @foreach($this->banks as $bank)
                        <flux:select.option :value="$bank->code">{{ $bank->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="bank_code" />
            </flux:field>

            {{-- Account number + verify --}}
            <flux:field>
                <flux:label>{{ __('Account Number') }}</flux:label>
                <div class="flex gap-2">
                    <flux:input
                        wire:model="account_number"
                        type="text"
                        inputmode="numeric"
                        maxlength="10"
                        placeholder="0123456789"
                        class="flex-1"
                    />
                    <flux:button
                        type="button"
                        wire:click="verifyAccount"
                        wire:loading.attr="disabled"
                        wire:target="verifyAccount"
                        variant="outline"
                    >
                        <span wire:loading.remove wire:target="verifyAccount">{{ __('Verify') }}</span>
                        <span wire:loading wire:target="verifyAccount">{{ __('Checking…') }}</span>
                    </flux:button>
                </div>
                <flux:error name="account_number" />
            </flux:field>

            {{-- Resolved account name --}}
            @if($resolved_account_name)
                <flux:callout icon="check-circle" color="green" variant="secondary">
                    <flux:callout.text>
                        {{ __('Account verified:') }} <strong>{{ $resolved_account_name }}</strong>
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2 pt-2">
                <flux:button :href="route('wallet.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button
                    type="submit"
                    variant="primary"
                    :disabled="! $account_verified"
                    wire:loading.attr="disabled"
                    wire:target="withdraw"
                >
                    {{ __('Confirm Withdrawal') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

    <flux:callout icon="information-circle" color="blue" variant="secondary">
        <flux:callout.text>
            {{ __('A processing fee of :fee applies. Transactions above :threshold also attract a stamp duty charge.', [
                'fee' => Number::currency($this->settings->withdrawal_fee),
                'threshold' => Number::currency($this->settings->stamp_duty_threshold),
            ]) }}
        </flux:callout.text>
    </flux:callout>
</div>
