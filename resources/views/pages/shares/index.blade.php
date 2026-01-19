<?php

use App\Settings\ShareSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public float $pricePerShare;

    public int $holdingPeriodDays;

    public function mount(ShareSettings $settings): void
    {
        $this->pricePerShare = $settings->share_price;
        $this->holdingPeriodDays = $settings->holding_period;
    }

    #[Computed]
    public function shares()
    {
        return Auth::user()->shares()
            ->where('quantity', '>', 0)
            ->latest()
            ->get();
    }

    #[Computed]
    public function totalShares()
    {
        return $this->shares->sum('quantity');
    }

    #[Computed]
    public function approvedSharesCount()
    {
        return $this->shares->where('status', \App\Enums\ShareStatus::APPROVED)->sum('quantity');
    }

    #[Computed]
    public function matureSharesCount()
    {
        $eligibilityDate = now()->subDays($this->holdingPeriodDays);

        return $this->shares
            ->where('status', \App\Enums\ShareStatus::APPROVED)
            ->filter(fn ($share) => $share->approved_at?->lte($eligibilityDate))
            ->sum('quantity');
    }

    #[Computed]
    public function totalValue()
    {
        return $this->totalShares * $this->pricePerShare;
    }

    public function render()
    {
        return $this->view()
            ->title('My Shares')
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-4xl mx-auto p-6">
    <x-page-header 
        heading="My Shares" 
        description="View and manage your share portfolio"
    >
        <x-slot name="actions">
            <x-ui.button wire:navigate href="{{ route('shares.buy') }}">
                Buy Shares
            </x-ui.button>
            <x-ui.button variant="outline" wire:navigate href="{{ route('shares.sell') }}">
                Sell Shares
            </x-ui.button>
        </x-slot>
    </x-page-header>

    <x-ui.card>

        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-ui.card class="bg-primary/5 border-primary/20 overflow-hidden">
                    <div class="text-center">
                        <span class="block text-sm text-gray-600 dark:text-neutral-400">Total Portfolio</span>
                        <span class="block text-xl sm:text-2xl font-bold text-primary truncate" title="{{ number_format($this->totalShares) }}">{{ number_format($this->totalShares) }}</span>
                    </div>
                </x-ui.card>
                <x-ui.card class="bg-success/5 border-success/20 overflow-hidden">
                    <div class="text-center">
                        <span class="block text-sm text-gray-600 dark:text-neutral-400">Mature Shares</span>
                        <span class="block text-xl sm:text-2xl font-bold text-success-600 truncate" title="{{ number_format($this->matureSharesCount) }}">{{ number_format($this->matureSharesCount) }}</span>
                        <span class="text-[10px] text-gray-500 uppercase tracking-tighter">Eligible for Dividends</span>
                    </div>
                </x-ui.card>
                <x-ui.card class="overflow-hidden border border-border">
                    <div class="text-center">
                        <span class="block text-sm text-gray-600 dark:text-neutral-400">Estimated Value</span>
                        <span class="block text-xl sm:text-2xl font-bold text-foreground truncate" title="{{ Number::currency($this->totalValue) }}">{{ Number::currency($this->totalValue) }}</span>
                    </div>
                </x-ui.card>
            </div>
        </div>

        @if($this->holdingPeriodDays > 0)
            <x-ui.alerts type="info" class="mb-6">
                <strong>Dividend Policy:</strong> Shares must be held for at least <strong>{{ $this->holdingPeriodDays }} days</strong> after approval to be eligible for dividends.
            </x-ui.alerts>
        @endif

        @if($this->shares->isEmpty())
            <x-ui.alerts type="info" class="mb-6">
                You don't own any shares yet.
            </x-ui.alerts>
        @else
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y border dark:border-gray-700 divide-gray-200">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Allocation Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Maturity</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->shares as $share)
                            @php
                                $isMature = $share->status === \App\Enums\ShareStatus::APPROVED && 
                                           $share->approved_at?->lte(now()->subDays($this->holdingPeriodDays));
                                $daysUntilMature = $share->approved_at 
                                    ? max(0, $this->holdingPeriodDays - $share->approved_at->diffInDays(now()))
                                    : null;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $share->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($share->quantity) }} Units
                                    <div class="text-[10px] text-gray-400 font-normal truncate max-w-[120px]" title="Value: {{ Number::currency($share->quantity * $this->pricePerShare) }}">
                                        Value: {{ Number::currency($share->quantity * $this->pricePerShare) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <x-ui.badge :color="$share->status->getColor()">
                                        {{ $share->status->getLabel() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($share->status === \App\Enums\ShareStatus::PENDING)
                                        <span class="text-gray-400 italic">Awaiting Approval</span>
                                    @elseif($isMature)
                                        <span class="text-success-600 font-medium inline-flex items-center">
                                            <x-ui.icon name="check-circle" class="w-4 h-4 mr-1" />
                                            Mature
                                        </span>
                                    @else
                                        <div class="flex flex-col">
                                            <span class="text-amber-600 font-medium">Immature</span>
                                            <span class="text-[10px] text-gray-500">Mature in {{ $daysUntilMature }} days</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</div>