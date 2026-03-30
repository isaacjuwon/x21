<?php

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Models\ShareHolding;
use App\Models\ShareOrder;
use App\Settings\ShareSettings;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?int $quantity = null;

    #[Computed]
    public function shareHolding(): ?ShareHolding
    {
        return Auth::user()->shareHolding;
    }

    #[Computed]
    public function shareSettings(): ShareSettings
    {
        return app(ShareSettings::class);
    }

    public function sell()
    {
        $this->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $userShares = $this->shareHolding?->quantity ?? 0;

        if ($userShares < $this->quantity) {
            $this->addError('quantity', __('You do not have enough shares to sell.'));

            return;
        }

        $totalAmount = $this->quantity * $this->shareSettings->price_per_share;

        ShareOrder::create([
            'user_id' => Auth::id(),
            'type' => ShareOrderType::Sell,
            'quantity' => $this->quantity,
            'price_per_share' => $this->shareSettings->price_per_share,
            'total_amount' => $totalAmount,
            'status' => ShareOrderStatus::Pending,
        ]);

        $this->quantity = null;
        $this->dispatch('modal-close', name: 'sell-shares');
        $this->dispatch('share-order-placed');

        Flux::toast(
            text: __('Share sell order placed successfully and is awaiting approval.'),
            variant: 'success',
        );
    }
}; ?>

<flux:modal name="sell-shares" class="md:w-[450px]">
    <form wire:submit="sell" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Sell Shares') }}</flux:heading>
            <flux:subheading>{{ __('Sell your shares at the current market price.') }}</flux:subheading>
        </div>

        <flux:input 
            wire:model.live="quantity" 
            type="number" 
            label="{{ __('Quantity') }}" 
            placeholder="0"
            min="1"
            :max="$this->shareHolding?->quantity ?? 0"
        />

        <div class="text-xs text-zinc-500">
            {{ __('Available Shares') }}: <span class="font-medium">{{ number_format($this->shareHolding?->quantity ?? 0) }}</span>
        </div>

        @if($quantity > 0)
            <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-500">{{ __('Price per share') }}</span>
                    <span class="font-medium">{{ Number::currency($this->shareSettings->price_per_share) }}</span>
                </div>
                <div class="flex justify-between text-sm border-t border-zinc-200 dark:border-zinc-800 pt-2">
                    <span class="text-zinc-500">{{ __('Total Proceeds') }}</span>
                    <span class="font-bold text-zinc-900 dark:text-white">{{ Number::currency($quantity * $this->shareSettings->price_per_share) }}</span>
                </div>
            </div>
        @endif

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">{{ __('Place Order') }}</flux:button>
        </div>
    </form>
</flux:modal>
