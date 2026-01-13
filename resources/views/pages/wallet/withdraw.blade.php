<?php

use App\Actions\GenerateReferenceAction;
use App\Actions\Wallet\WithdrawWalletAction;
use App\Enums\Connectors\PaymentConnector;
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
        try {
            return PaymentConnector::default()
                ->connector()
                ->bank()
                ->list(country: 'nigeria');
        } catch (\Exception $e) {
            $this->toastError('Failed to load banks. Please refresh the page.');
            return collect();
        }
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

    <div data-slot="card" class="p-6 bg-background-content rounded-3xl border border-border shadow-sm">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label>{{ __('Account Holder Name') }}</x-ui.label>
                <x-ui.input 
                    type="text"
                    value="{{ auth()->user()->name }}"
                    readonly
                    class="bg-background opacity-70"
                 />
                <p class="text-[10px] text-foreground-content mt-1 font-bold uppercase tracking-wider">Your bank account must be registered in this name</p>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Amount (NGN)') }}</x-ui.label>
                <x-ui.input 
                    wire:model="amount" 
                    type="number"
                    autofocus
                    min="100"
                    placeholder="Enter amount to withdraw"
                    class="bg-background"
                 />
                <x-ui.error name="amount" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Account Number') }}</x-ui.label>
                <x-ui.input 
                    wire:model="account_number" 
                    type="text"
                    placeholder="Enter account number"
                    class="bg-background"
                 />
                <x-ui.error name="account_number" />
            </x-ui.field>

             <!-- Bank Select -->
            <x-ui.field>
                <x-ui.label>{{ __('Bank') }}</x-ui.label>
                <select wire:model="bank_code" class="w-full bg-background border-2 border-border text-foreground rounded-2xl p-4 focus:ring-4 focus:ring-primary/10 transition-all font-bold">
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
