<?php

use App\Settings\ShareSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate(['required', 'integer', 'min:1'])]
    public int $quantity = 1;

    public float $pricePerShare;

    public function mount(ShareSettings $settings): void
    {
        $this->pricePerShare = $settings->share_price;
    }

    #[Computed]
    public function totalCost()
    {
        return $this->quantity * $this->pricePerShare;
    }

    #[Computed]
    public function walletBalance()
    {
        return Auth::user()->walletBalance;
    }

    public function updatedQuantity()
    {
        $this->validate([
            'quantity' => 'required|integer|min:1',
        ]);
    }

    public function buy()
    {
        $this->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        if (! $user->canAfford($this->totalCost)) {
            $this->addError('quantity', 'Insufficient funds in your wallet.');

            return;
        }

        try {
            $user->buyShares($this->quantity);
            session()->flash('message', "Successfully purchased {$this->quantity} shares.");
            $this->redirect(route('shares.index'), navigate: true);
        } catch (\Exception $e) {
            $this->addError('quantity', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        return $this->view()
            ->title('Buy Shares')
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-md mx-auto p-6">
    <x-page-header 
        heading="Buy Shares" 
        description="Purchase shares to grow your portfolio"
    />

    @php
        $settings = app(App\Settings\ShareSettings::class);
    @endphp

    @if($settings->holding_period > 0)
        <x-ui.alerts type="warning" class="mb-6">
            <strong>Note:</strong> Shares purchased today will be eligible for dividends after <strong>{{ $settings->holding_period }} days</strong> of holding from the date of approval.
        </x-ui.alerts>
    @endif

    <x-ui.card>

        <div class="mb-6">
            <x-ui.card class="bg-neutral-50 dark:bg-neutral-900/50 border-neutral-100 dark:border-neutral-700 shadow-none">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Wallet Balance:</span>
                        <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ Number::currency($this->walletBalance, 'NGN') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Price per Share:</span>
                        <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ Number::currency($pricePerShare, 'NGN') }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <form wire:submit="buy">
            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Quantity') }}</x-ui.label>
                    <x-ui.input
                        wire:model.live="quantity"
                        type="number"
                        min="1"
                        placeholder="Enter number of shares"
                        class="text-base font-bold tracking-widest h-14"
                        required
                    />
                    <x-ui.error name="quantity" />
                </x-ui.field>

                <div class="flex justify-between items-center border-t border-neutral-100 dark:border-neutral-700 pt-6 mt-2">
                    <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Cost:</span>
                    <span class="text-2xl font-bold text-primary">{{ Number::currency($this->totalCost, 'NGN') }}</span>
                </div>

                <div class="flex gap-4 pt-4">
                    <x-ui.button type="submit" class="flex-1 h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20">
                        Confirm Purchase
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:navigate href="{{ route('shares.index') }}" class="flex-1 h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">
                        Cancel
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>
</div>