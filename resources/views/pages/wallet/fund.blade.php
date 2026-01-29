<?php

use App\Actions\GenerateReferenceAction;
use App\Actions\Wallet\FundWalletAction;
use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    use HasToast;

    #[Rule('required|numeric|min:100')]
    public float|int $amount = 0;

    public function save(FundWalletAction $fundWalletAction, GenerateReferenceAction $generateReferenceAction)
    {
        $this->validate();

        $data = [
            'amount' => $this->amount,
            'email' => auth()->user()->email,
            'reference' => $generateReferenceAction->handle('FUND'),
        ];

        $result = $fundWalletAction->handle($data);

        if ($result->isError()) {
            $this->toastError($result->error->getMessage());

            return;
        }

        $response = $result->unwrap();

        if (empty($response['authorization_url'])) {
            $this->toastError('Failed to generate payment URL.');

            return;
        }

        // Redirect to Paystack
        $this->redirect($response['authorization_url']);
    }
}; ?>

<div class="max-w-xl mx-auto p-6">
    <x-page-header 
        heading="Fund Wallet" 
        description="Add funds to your wallet securely"
    />

    <div data-slot="card" class="p-6 bg-white dark:bg-neutral-800 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label>{{ __('Amount (NGN)') }}</x-ui.label>
                <x-ui.input 
                    wire:model="amount" 
                    type="number"
                    autofocus
                    min="100"
                    placeholder="Enter amount to fund"
                    class="bg-neutral-50 dark:bg-neutral-900/50 font-bold"
                 />
                <x-ui.error name="amount" />
                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-1 font-bold uppercase tracking-widest">Minimum funding amount is ₦100</p>
            </x-ui.field>

            <div class="flex justify-end gap-3">
                <x-ui.button tag="a" href="{{ route('wallet.index') }}" variant="outline">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    Proceed to Payment
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
