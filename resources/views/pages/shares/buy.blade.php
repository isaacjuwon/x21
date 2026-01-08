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

        <div class="mb-4">
            <x-ui.card>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Wallet Balance:</span>
                        <span class="font-bold text-gray-800">{{ number_format($this->walletBalance, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Price per Share:</span>
                        <span class="font-bold text-gray-800">{{ number_format($pricePerShare, 2) }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <form wire:submit="buy">
            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>{{ __('Quantity') }}</x-ui.label>
                    <x-ui.input
                        wire:model.live="quantity"
                        type="number"
                        min="1"
                        placeholder="Enter number of shares"
                        required
                    />
                    <x-ui.error name="quantity" />
                </x-ui.field>

                <div class="flex justify-between items-center border-t border-gray-200 pt-4">
                    <span class="text-lg font-semibold text-gray-700">Total Cost:</span>
                    <span class="text-xl font-bold text-primary">{{ Number::currency($this->totalCost) }}</span>
                </div>

                <div class="flex gap-3">
                    <x-ui.button type="submit" class="flex-1">
                        Confirm Purchase
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:navigate href="{{ route('shares.index') }}">
                        Cancel
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>
</div>