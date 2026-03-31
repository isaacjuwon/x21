<?php

use App\Models\ShareHolding;
use App\Settings\ShareSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Shares'), Defer] class extends Component
{
    use WithPagination;

    #[Url(as: 'page')]
    public $page = 1;

    #[Url]
    public $sortBy = 'created_at';

    #[Url]
    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[On('share-order-placed')]
    public function refresh()
    {
        // This will trigger a re-render and update computed properties
    }

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

    #[Computed]
    public function shareOrders()
    {
        return Auth::user()->shareOrders()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="space-y-6 animate-pulse">
            <div class="flex items-center justify-between">
                <div class="h-8 w-32 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                <div class="flex space-x-2">
                    <div class="h-10 w-24 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
            </div>
            <div class="h-64 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('My Shares') }}</flux:heading>

        <div class="flex space-x-2">
            <flux:modal.trigger name="sell-shares">
                <flux:button variant="outline" icon="minus">
                    {{ __('Sell Shares') }}
                </flux:button>
            </flux:modal.trigger>

            <flux:modal.trigger name="buy-shares">
                <flux:button variant="primary" icon="plus">
                    {{ __('Buy Shares') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <livewire:shares.buy-modal />
    <livewire:shares.sell-modal />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Total Shares') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ number_format($this->shareHolding?->quantity ?? 0) }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Current Value') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ Number::currency(($this->shareHolding?->quantity ?? 0) * $this->shareSettings->price_per_share) }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Price per Share') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ Number::currency($this->shareSettings->price_per_share) }}
            </flux:text>
        </flux:card>
    </div>

    <flux:card class="p-2! overflow-hidden border-zinc-200 dark:border-zinc-800">
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
            <flux:heading size="lg">{{ __('Share Orders') }}</flux:heading>
            <flux:subheading>{{ __('History of your share purchase and sell orders.') }}</flux:subheading>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Quantity') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Price') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Total') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Status') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->shareOrders as $order)
                    <flux:table.row :key="$order->id">
                        <flux:table.cell class="text-zinc-500">
                            {{ $order->created_at->format('M j, Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$order->type->getFluxColor()" size="sm">{{ $order->type->getLabel() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ number_format($order->quantity) }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ Number::currency($order->price_per_share) }}
                        </flux:table.cell>
                        <flux:table.cell align="end" class="font-medium text-zinc-900 dark:text-white">
                            {{ Number::currency($order->total_amount) }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:badge :color="$order->status->getFluxColor()" inset="top bottom">
                                {{ $order->status->getLabel() }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-10 text-zinc-500">
                            {{ __('No share orders found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->shareOrders->hasPages())
            <div class="p-6 border-t border-zinc-200 dark:border-zinc-800">
                {{ $this->shareOrders->links() }}
            </div>
        @endif
    </flux:card>
</div>
