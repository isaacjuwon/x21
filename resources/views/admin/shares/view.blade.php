<?php

use App\Livewire\Concerns\HasToast;
use App\Models\Share;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public Share $share;

    #[Rule('required|integer|min:1')]
    public int $quantity = 1;

    #[Rule('required|string')]
    public string $currency = 'Units';

    public function mount(Share $share)
    {
        $this->share = $share;
        $this->quantity = $share->quantity;
        $this->currency = $share->currency;
    }

    #[\Livewire\Attributes\Computed]
    public function nextDividend()
    {
        return \App\Models\Dividend::where('currency', $this->share->currency)
            ->where('declaration_date', '>', now())
            ->orWhere(function ($query) {
                $query->where('paid_out', false)
                    ->where('payment_date', '>=', now());
            })
            ->orderBy('payment_date', 'asc')
            ->first();
    }

    #[\Livewire\Attributes\Computed]
    public function paymentHistory()
    {
        return \App\Models\DividendPayment::where('holder_type', $this->share->holder_type)
            ->where('holder_id', $this->share->holder_id)
            ->with('dividend')
            ->latest('paid_at')
            ->take(10)
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function totalDividends(): float
    {
        return \App\Models\DividendPayment::where('holder_type', $this->share->holder_type)
            ->where('holder_id', $this->share->holder_id)
            ->sum('total_amount');
    }

    public function approve()
    {
        if ($this->share->status !== \App\Enums\ShareStatus::PENDING) {
            return;
        }

        $this->share->update([
            'status' => \App\Enums\ShareStatus::APPROVED,
            'approved_at' => now(),
        ]);

        // Calculate new balance (total shares for this currency)
        $newBalance = $this->share->holder->shares()
            ->where('currency', $this->share->currency)
            ->sum('quantity');

        event(new \App\Events\Shares\SharesApproved(
            $this->share->holder,
            $this->share->quantity,
            $this->share->currency,
            (int) $newBalance
        ));

        $this->toastSuccess('Share approved successfully.');
    }

    public function reject()
    {
        if ($this->share->status !== \App\Enums\ShareStatus::PENDING) {
            return;
        }

        $this->share->update([
            'status' => \App\Enums\ShareStatus::REJECTED,
        ]);

        // Balance doesn't change on rejection, but we still need to pass it
        $currentBalance = $this->share->holder->getApprovedSharesCount($this->share->currency);

        event(new \App\Events\Shares\SharesRejected(
            $this->share->holder,
            $this->share->quantity,
            $this->share->currency,
            (int) $currentBalance
        ));

        $this->toastSuccess('Share rejected.');
    }

    public function save()
    {
        $this->validate();

        $this->share->update([
            'quantity' => $this->quantity,
            'currency' => $this->currency,
        ]);

        $this->toastSuccess('Share updated successfully.');
    }

    public function delete()
    {
        $this->share->delete();
        $this->toastSuccess('Share deleted successfully.');

        return redirect()->route('admin.shares.index');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-5xl mx-auto p-6 space-y-8">
    <x-page-header 
        :heading="'Share Allocation #' . $share->id" 
        :description="'Manage shareholding for ' . ($share->holder->name ?? 'Unknown')"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                @if($share->status === \App\Enums\ShareStatus::PENDING)
                    <x-ui.button 
                        type="button" 
                        wire:click="approve" 
                        wire:confirm="Are you sure you want to approve this share allocation?"
                        variant="primary" 
                    >
                        Approve Allocation
                    </x-ui.button>
                    <x-ui.button 
                        type="button" 
                        wire:click="reject" 
                        wire:confirm="Are you sure you want to reject this share allocation?"
                        variant="ghost" 
                        class="text-red-600 hover:text-red-700"
                    >
                        Reject
                    </x-ui.button>
                @endif

                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this share?"
                    variant="ghost"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <x-ui.icon name="trash" class="w-4 h-4 mr-2" />
                    Delete
                </x-ui.button>
                <x-ui.button tag="a" href="{{ route('admin.shares.index') }}" variant="outline">

<div class="max-w-5xl mx-auto p-6 space-y-8">
    <x-page-header 
        :heading="'Share Allocation #' . $share->id" 
        :description="'Manage shareholding for ' . ($share->holder->name ?? 'Unknown')"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                @if($share->status === \App\Enums\ShareStatus::PENDING)
                    <x-ui.button 
                        type="button" 
                        wire:click="approve" 
                        wire:confirm="Are you sure you want to approve this share allocation?"
                        variant="primary" 
                    >
                        Approve Allocation
                    </x-ui.button>
                    <x-ui.button 
                        type="button" 
                        wire:click="reject" 
                        wire:confirm="Are you sure you want to reject this share allocation?"
                        variant="ghost" 
                        class="text-red-600 hover:text-red-700"
                    >
                        Reject
                    </x-ui.button>
                @endif

                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this share?"
                    variant="ghost"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <x-ui.icon name="trash" class="w-4 h-4 mr-2" />
                    Delete
                </x-ui.button>
                <x-ui.button tag="a" href="{{ route('admin.shares.index') }}" variant="outline">
                    Back to List
                </x-ui.button>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-neutral-800 p-6 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary/10 dark:bg-primary/20 rounded-[--radius-field] flex items-center justify-center text-primary dark:text-primary-400">
                    <x-ui.icon name="chart-pie" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Shares</p>
                    <p class="text-xl font-bold text-neutral-900 dark:text-white">{{ number_format($share->quantity) }} <span class="text-xs font-normal text-neutral-500">{{ $share->currency }}</span></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 p-6 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-success/10 dark:bg-success/20 rounded-[--radius-field] flex items-center justify-center text-success dark:text-success-400">
                    <x-ui.icon name="banknotes" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Total Dividends</p>
                    <p class="text-xl font-bold text-neutral-900 dark:text-white">{{ \Illuminate\Support\Number::currency($this->totalDividends) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 p-6 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-accent/10 dark:bg-accent/20 rounded-[--radius-field] flex items-center justify-center text-accent dark:text-accent-400">
                    <x-ui.icon name="calendar" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Next Payment</p>
                    <p class="text-lg font-bold text-neutral-900 dark:text-white">
                        {{ $this->nextDividend?->payment_date?->format('M d, Y') ?? 'Not scheduled' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Details and Next Payment --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Next Payment Card --}}
            @if($this->nextDividend)
                <div class="bg-gradient-to-br from-secondary to-secondary-fg rounded-[--radius-box] p-6 text-white shadow-lg shadow-secondary/20">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-bold opacity-90 text-white">Upcoming Dividend</h3>
                            <p class="text-2xl font-extrabold mt-1 text-white">{{ \Illuminate\Support\Number::currency($share->quantity * $this->nextDividend->amount_per_share) }}</p>
                        </div>
                        <div class="bg-white/20 p-2 rounded-[--radius-field] backdrop-blur-sm">
                            <x-ui.icon name="bolt" class="w-6 h-6" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="bg-white/10 rounded-[--radius-field] p-3">
                            <p class="opacity-70">Payment Date</p>
                            <p class="font-bold text-white">{{ $this->nextDividend->payment_date->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-white/10 rounded-[--radius-field] p-3 text-white">
                            <p class="opacity-70">Record Date</p>
                            <p class="font-bold text-white">{{ $this->nextDividend->record_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Payment History --}}
            <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                    <h3 class="font-bold text-neutral-900 dark:text-white">Dividend History</h3>
                    <x-ui.badge color="neutral">{{ $this->paymentHistory->count() }} payments</x-ui.badge>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-neutral-50/50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 text-left">
                                <th class="px-6 py-3 font-bold">Date</th>
                                <th class="px-6 py-3 font-bold">Type</th>
                                <th class="px-6 py-3 font-bold">Rate</th>
                                <th class="px-6 py-3 font-bold text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                            @forelse($this->paymentHistory as $payment)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-6 py-4 text-neutral-900 dark:text-white">
                                        {{ $payment->paid_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-ui.badge color="neutral">{{ $payment->dividend->type }}</x-ui.badge>
                                    </td>
                                    <td class="px-6 py-4 text-neutral-500">
                                        {{ number_format($payment->amount_per_share, 4) }}/sh
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-neutral-900 dark:text-white">
                                        {{ \Illuminate\Support\Number::currency($payment->total_amount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-neutral-400">
                                        No dividend payments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: Side Actions --}}
        <div class="space-y-8">
            {{-- Edit Form --}}
            <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm p-6">
                <h3 class="font-bold text-neutral-900 dark:text-white mb-6">Manage Allocation</h3>
                <form wire:submit="save" class="space-y-4">
                    <x-ui.field>
                        <x-ui.label for="quantity">Quantity</x-ui.label>
                        <x-ui.input 
                            wire:model="quantity" 
                            id="quantity" 
                            type="number" 
                            min="1" 
                            class="bg-neutral-50 dark:bg-neutral-900/50"
                        />
                        <x-ui.error name="quantity" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="currency">Currency Unit</x-ui.label>
                        <x-ui.input 
                            wire:model="currency" 
                            id="currency" 
                            readonly 
                            class="bg-neutral-100 dark:bg-neutral-900 cursor-not-allowed"
                        />
                        <x-ui.error name="currency" />
                    </x-ui.field>

                    <div class="pt-4">
                        <x-ui.button type="submit" variant="primary" class="w-full">
                            Update Allocation
                        </x-ui.button>
                    </div>
                </form>
            </div>

            {{-- Shareholder Quick Info --}}
            <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-[--radius-box] p-6 border border-neutral-100 dark:border-neutral-100/10">
                <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-4">Shareholder</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-primary-400 font-bold">
                        {{ strtoupper(substr($share->holder->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-neutral-900 dark:text-white leading-none">{{ $share->holder->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-neutral-500 mt-1 uppercase">{{ str_replace('App\\Models\\', '', $share->holder_type) }}</p>
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700 space-y-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-neutral-500">Status</span>
                        <x-ui.badge :color="$share->status->getColor()">{{ $share->status->getLabel() }}</x-ui.badge>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-neutral-500">Allocated</span>
                        <span class="text-neutral-900 dark:text-white font-bold">{{ $share->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Market Info --}}
            <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 shadow-sm p-6">
                <h3 class="font-bold text-neutral-900 dark:text-white mb-4">Market Overview</h3>
                <div class="space-y-4">
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-field] flex justify-between items-center">
                        <span class="text-xs text-neutral-500 font-bold uppercase">Current Price</span>
                        <span class="font-bold text-neutral-900 dark:text-white">{{ \Illuminate\Support\Number::currency($shareSettings->share_price) }}</span>
                    </div>
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-field] flex justify-between items-center">
                        <span class="text-xs text-neutral-500 font-bold uppercase">Interest Rate</span>
                        <span class="font-bold text-success">{{ number_format($shareSettings->share_interest_rate, 2) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
