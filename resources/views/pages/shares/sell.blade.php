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

        <div class="mb-6">
            <x-ui.card class="bg-neutral-50 dark:bg-neutral-900/50 border-neutral-100 dark:border-neutral-700 shadow-none">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Shares Owned:</span>
                        <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ number_format($this->ownedShares) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Current Price:</span>
                        <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ Number::currency($pricePerShare, 'NGN') }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <form wire:submit="sell">
            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Quantity to Sell') }}</x-ui.label>
                    <x-ui.input
                        wire:model.live="quantity"
                        type="number"
                        min="1"
                        :max="$this->ownedShares"
                        placeholder="0"
                        class="text-base font-bold tracking-widest h-14"
                        required
                    />
                    <x-ui.error name="quantity" />
                </x-ui.field>

                <div class="flex justify-between items-center border-t border-neutral-100 dark:border-neutral-700 pt-6 mt-2">
                    <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Value:</span>
                    <span class="text-2xl font-bold text-success">{{ Number::currency($this->totalValue, 'NGN') }}</span>
                </div>

                <div class="flex gap-4 pt-4">
                    <x-ui.button type="submit" class="flex-1 h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-success/20">
                        Confirm Sale
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:navigate href="{{ route('shares.index') }}" class="flex-1 h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">
                        Cancel
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>
</div>