<?php

use App\Enums\Shares\ShareOrderStatus;
use App\Enums\Shares\ShareOrderType;
use App\Enums\Wallets\WalletType;
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
    public function shareSettings(): ShareSettings
    {
        return app(ShareSettings::class);
    }

    public function buy()
    {
        $this->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $totalAmount = $this->quantity * $this->shareSettings->price_per_share;
        $wallet = Auth::user()->getWallet(WalletType::General);

        if ($wallet->available_balance < $totalAmount) {
            $this->addError('quantity', __('Insufficient balance in your general wallet.'));

            return;
        }

        ShareOrder::create([
            'user_id' => Auth::id(),
            'type' => ShareOrderType::Buy,
            'quantity' => $this->quantity,
            'price_per_share' => $this->shareSettings->price_per_share,
            'total_amount' => $totalAmount,
            'status' => ShareOrderStatus::Pending,
        ]);

        $this->quantity = null;
        $this->dispatch('modal-close', name: 'buy-shares');
        $this->dispatch('share-order-placed');

        Flux::toast(
            text: __('Share purchase order placed successfully and is awaiting approval.'),
            variant: 'success',
        );
    }
}; ?>

<flux:modal name="buy-shares" class="md:w-[450px]">
    <form wire:submit="buy" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Buy Shares') }}</flux:heading>
            <flux:subheading>{{ __('Purchase more shares at the current market price.') }}</flux:subheading>
        </div>

        <flux:input 
            wire:model.live="quantity" 
            type="number" 
            label="{{ __('Quantity') }}" 
            placeholder="0"
            min="1"
        />

        @if($quantity > 0)
            <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-500">{{ __('Price per share') }}</span>
                    <span class="font-medium">{{ Number::currency($this->shareSettings->price_per_share) }}</span>
                </div>
                <div class="flex justify-between text-sm border-t border-zinc-200 dark:border-zinc-800 pt-2">
                    <span class="text-zinc-500">{{ __('Total Cost') }}</span>
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
