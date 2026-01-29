<?php

use App\Actions\GenerateReferenceAction;
use App\Actions\Wallet\WithdrawWalletAction;
use App\Livewire\Concerns\HasToast;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    use HasToast;

    #[Rule('required|numeric|min:100')]
    public float|int $amount = 0;

    #[Rule('required|string')]
    public string $account_number = '';

    #[Rule('required|string')]
    public string $bank_code = '';

    #[Computed]
    public function banks(): Collection
    {
        return app(\App\Actions\Wallet\FetchBanksAction::class)->handle();
    }

    #[Computed]
    public function charges(): array
    {
        $settings = app(\App\Settings\WalletSettings::class);
        $fee = ($this->amount * ($settings->withdrawal_fee_percentage / 100));

        if ($settings->withdrawal_fee_cap > 0 && $fee > $settings->withdrawal_fee_cap) {
            $fee = $settings->withdrawal_fee_cap;
        }

        return [
            'fee' => $fee,
            'total' => $this->amount + $fee,
        ];
    }

    public function save(WithdrawWalletAction $withdrawWalletAction, GenerateReferenceAction $generateReferenceAction)
    {
        $this->validate();

        $data = [
            'amount' => $this->amount,
            'account_number' => $this->account_number,
            'bank_code' => $this->bank_code,
            'reference' => $generateReferenceAction->handle('WDR'),
        ];

        $result = $withdrawWalletAction->handle($data);

        if ($result->isError()) {
            $this->toastError($result->error->getMessage());

            return;
        }

        $this->toastSuccess('Withdrawal initiated successfully.');

        return redirect()->route('wallet.index');
    }
}; ?>

<div class="max-w-xl mx-auto p-6">
    <x-page-header 
        heading="Withdraw Funds" 
        description="Withdraw funds from your wallet to your bank account"
    />

    <div data-slot="card" class="p-6 bg-white dark:bg-neutral-800 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label>{{ __('Account Holder Name') }}</x-ui.label>
                <x-ui.input 
                    type="text"
                    value="{{ auth()->user()->name }}"
                    readonly
                    class="bg-neutral-50 dark:bg-neutral-900/50 opacity-70 font-bold"
                 />
                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-1 font-bold uppercase tracking-widest">Your bank account must be registered in this name</p>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Amount (NGN)') }}</x-ui.label>
                <x-ui.input 
                    wire:model.live.debounce.300ms="amount" 
                    type="number"
                    autofocus
                    min="100"
                    placeholder="Enter amount to withdraw"
                    class="bg-neutral-50 dark:bg-neutral-900/50 font-bold"
                 />
                <x-ui.error name="amount" />
                
                @if($amount > 0)
                    <div class="mt-3 p-3 bg-primary/5 rounded-[--radius-box] border border-primary/10 space-y-2">
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest">
                            <span class="text-neutral-500 dark:text-neutral-400">Withdrawal Fee:</span>
                            <span class="text-neutral-900 dark:text-white">{{ Number::currency($this->charges['fee']) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold border-t border-primary/10 pt-2">
                            <span class="text-neutral-500 dark:text-neutral-400">Total Deduction:</span>
                            <span class="text-primary">{{ Number::currency($this->charges['total']) }}</span>
                        </div>
                    </div>
                @endif
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Account Number') }}</x-ui.label>
                <x-ui.input 
                    wire:model="account_number" 
                    type="text"
                    placeholder="Enter account number"
                    class="bg-neutral-50 dark:bg-neutral-900/50 font-bold"
                 />
                <x-ui.error name="account_number" />
            </x-ui.field>

             <!-- Bank Select -->
            <x-ui.field>
                <x-ui.label>{{ __('Bank') }}</x-ui.label>
                <select wire:model="bank_code" class="w-full bg-neutral-50 dark:bg-neutral-900/50 border-2 border-neutral-100 dark:border-neutral-700 text-neutral-900 dark:text-white rounded-[--radius-field] p-4 focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                    <option value="">Select Bank</option>
                    @foreach($this->banks as $bank)
                        <option value="{{ $bank->code }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
                <x-ui.error name="bank_code" />
            </x-ui.field>

            <div class="flex justify-end gap-3">
                <x-ui.button tag="a" href="{{ route('wallet.index') }}" variant="outline">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    Withdraw Funds
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
