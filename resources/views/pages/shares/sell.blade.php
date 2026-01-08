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
    public function totalValue()
    {
        return $this->quantity * $this->pricePerShare;
    }

    #[Computed]
    public function ownedShares()
    {
        return Auth::user()->getApprovedSharesCount();
    }

    public function updatedQuantity()
    {
        $this->validate([
            'quantity' => 'required|integer|min:1',
        ]);
    }

    public function sell()
    {
        $this->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $approvedCount = $user->getApprovedSharesCount();

        if ($approvedCount < $this->quantity) {
            $this->addError('quantity', 'Insufficient approved shares to sell.');

            return;
        }

        try {
            $user->sellShares($this->quantity);
            session()->flash('message', "Successfully sold {$this->quantity} shares.");
            $this->redirect(route('shares.index'), navigate: true);
        } catch (\Exception $e) {
            $this->addError('quantity', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        return $this->view()
            ->title('Sell Shares')
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-md mx-auto p-6">
    <x-page-header 
        heading="Sell Shares" 
        description="Sell approved shares from your portfolio"
    />

    <x-ui.card>

        <div class="mb-4">
            <x-ui.card>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Shares Owned:</span>
                        <span class="font-bold text-gray-800">{{ $this->ownedShares }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Current Price:</span>
                        <span class="font-bold text-gray-800">{{ number_format($pricePerShare, 2) }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <form wire:submit="sell">
            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>{{ __('Quantity to Sell') }}</x-ui.label>
                    <x-ui.input
                        wire:model.live="quantity"
                        type="number"
                        min="1"
                        :max="$this->ownedShares"
                        placeholder="Enter number of shares to sell"
                        required
                    />
                    <x-ui.error name="quantity" />
                </x-ui.field>

                <div class="flex justify-between items-center border-t border-gray-200 pt-4">
                    <span class="text-lg font-semibold text-gray-700">Total Value:</span>
                    <span class="text-xl font-bold text-success-600">{{ Number::currency($this->totalValue) }}</span>
                </div>

                <div class="flex gap-3">
                    <x-ui.button type="submit" class="flex-1">
                        Confirm Sale
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:navigate href="{{ route('shares.index') }}">
                        Cancel
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>
</div>