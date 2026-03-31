<?php

use App\Models\Loan;
use App\Models\ShareHolding;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Settings\ShareSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard'), Defer] class extends Component {
    /**
     * Get the user's wallet.
     */
    #[Computed]
    public function wallet(): ?Wallet
    {
        return Auth::user()->wallet;
    }

    /**
     * Get the user's share holdings.
     */
    #[Computed]
    public function shareHolding(): ?ShareHolding
    {
        return Auth::user()->shareHolding;
    }

    /**
     * Get the user's active loans.
     */
    #[Computed]
    public function activeLoans()
    {
        return Auth::user()->loans()->where('status', 'active')->get();
    }

    /**
     * Get the total loan balance.
     */
    #[Computed]
    public function loanBalance(): float
    {
        return $this->activeLoans->sum('outstanding_balance');
    }

    /**
     * Get the share price.
     */
    #[Computed]
    public function sharePrice(): float
    {
        return app(ShareSettings::class)->price_per_share;
    }

    /**
     * Get the total value of shares.
     */
    #[Computed]
    public function sharesValue(): float
    {
        return ($this->shareHolding?->quantity ?? 0) * $this->sharePrice;
    }

    /**
     * Get recent transactions.
     */
    #[Computed]
    public function recentTransactions()
    {
        return Auth::user()->transactions()->latest()->take(5)->get();
    }

    /**
     * Get quick stats.
     */
    #[Computed]
    public function stats(): array
    {
        return [
            [
                'label' => 'Total Balance',
                'value' => Number::currency($this->wallet?->balance ?? 0),
                'description' => 'Available in your wallet',
                'icon' => 'wallet',
                'color' => 'primary',
            ],
            [
                'label' => 'Active Loans',
                'value' => Number::currency($this->loanBalance),
                'description' => $this->activeLoans->count().' active loan(s)',
                'icon' => 'banknotes',
                'color' => 'orange',
            ],
            [
                'label' => 'Shares Value',
                'value' => Number::currency($this->sharesValue),
                'description' => number_format($this->shareHolding?->quantity ?? 0).' share(s) held',
                'icon' => 'chart-bar',
                'color' => 'green',
            ],
        ];
    }

    public function placeholder()
    {
        return <<<'HTML'
            <div class="flex h-full w-full flex-1 flex-col gap-4">
                <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div class="h-32 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                    <div class="h-32 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                    <div class="h-32 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                </div>
                <div class="h-64 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
            </div>
        HTML;
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <!-- Header with Quick Actions -->
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <flux:heading size="xl" level="1">Welcome back, {{ auth()->user()->name }}</flux:heading>
                <flux:subheading>Here is what's happening with your account today.</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:button icon="plus" variant="primary" href="{{ route('wallet.transfer') }}" wire:navigate>Transfer</flux:button>
                <flux:button icon="banknotes" href="{{ route('loan.apply') }}" wire:navigate>Apply for Loan</flux:button>
            </div>
        </div>

        @if(!$this->wallet || $this->wallet->balance == 0)
            <flux:callout icon="information-circle" color="blue" variant="secondary">
                <flux:callout.heading>Your wallet is empty</flux:callout.heading>
                <flux:callout.text>Add funds to your wallet to start using our services like airtime, data, and bill payments.</flux:callout.text>
                <x-slot name="actions">
                    <flux:button size="sm" href="{{ route('wallet.fund') }}" wire:navigate>Add Funds</flux:button>
                </x-slot>
            </flux:callout>
        @endif
    </div>

    <!-- Summary Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($this->stats as $stat)
            <flux:card class="p-6 flex items-center gap-4 border-zinc-200 dark:border-zinc-800">
                <div class="p-3 bg-{{ $stat['color'] }}-500/10 rounded-xl">
                    <flux:icon :name="$stat['icon']" class="size-6 text-{{ $stat['color'] }}-500" />
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ $stat['label'] }}</flux:text>
                    <div class="text-2xl font-bold tracking-tight">{{ $stat['value'] }}</div>
                    <flux:text class="text-xs text-zinc-400">{{ $stat['description'] }}</flux:text>
                </div>
            </flux:card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content: Recent Transactions -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">Recent Transactions</flux:heading>
                <flux:button variant="ghost" size="sm" href="{{ route('wallet.index') }}" wire:navigate>View All</flux:button>
            </div>

            <flux:card class="p-2 overflow-hidden border-zinc-200 dark:border-zinc-800">
                @if($this->recentTransactions->count() > 0)
                    <flux:table>
                        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
                            <flux:table.column>Type</flux:table.column>
                            <flux:table.column align="end">Amount</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Date</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach($this->recentTransactions as $transaction)
                                <flux:table.row :key="$transaction->id">
                                    <flux:table.cell class="flex items-center gap-2">
                                        <flux:icon :name="$transaction->type->getFluxIcon()" class="size-4 text-zinc-400" />
                                        <span>{{ $transaction->type->getLabel() }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell align="end" variant="strong" class="{{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->amount > 0 ? '+' : '' }}{{ Number::currency($transaction->amount) }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$transaction->status->getFluxColor()" size="sm" inset="top bottom">
                                            {{ $transaction->status->getLabel() }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-zinc-500 text-sm whitespace-nowrap">
                                        {{ $transaction->created_at->diffForHumans() }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @else
                    <div class="p-12 text-center">
                        <flux:icon.layout-grid class="size-12 text-zinc-200 dark:text-zinc-800 mx-auto mb-4" />
                        <flux:text>No transactions found.</flux:text>
                    </div>
                @endif
            </flux:card>
        </div>

        <!-- Sidebar: Product Cards -->
        <div class="space-y-6">
            <!-- Wallet Card -->
            <flux:card class="p-6 space-y-4 bg-primary-color/5 border-primary-color/20">
                <div class="flex justify-between items-start">
                    <flux:heading size="sm" class="text-primary-color uppercase tracking-widest font-bold">Wallet</flux:heading>
                    <flux:icon.wallet class="size-5 text-primary-color" />
                </div>
                <div>
                    <div class="text-3xl font-bold tracking-tight text-primary-color">{{ Number::currency($this->wallet?->balance ?? 0) }}</div>
                    <flux:text class="text-xs text-primary-color/60">Available Balance</flux:text>
                </div>
                <div class="flex gap-2 pt-2">
                    <flux:button variant="primary" size="sm" class="flex-1" href="{{ route('wallet.transfer') }}" wire:navigate>Transfer</flux:button>
                    <flux:button variant="outline" size="sm" class="flex-1" href="{{ route('wallet.withdraw') }}" wire:navigate>Withdraw</flux:button>
                </div>
            </flux:card>

            <!-- Shares Card -->
            <flux:card class="p-6 space-y-4 border-zinc-200 dark:border-zinc-800">
                <div class="flex justify-between items-start">
                    <flux:heading size="sm" class="uppercase tracking-widest font-bold text-zinc-500">Shares</flux:heading>
                    <flux:icon.chart-bar class="size-5 text-green-500" />
                </div>
                <div class="flex justify-between items-end">
                    <div>
                        <div class="text-2xl font-bold tracking-tight">{{ number_format($this->shareHolding?->quantity ?? 0) }}</div>
                        <flux:text class="text-xs text-zinc-400">Total Units</flux:text>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold text-green-600">{{ Number::currency($this->sharesValue) }}</div>
                        <flux:text class="text-xs text-zinc-400">Market Value</flux:text>
                    </div>
                </div>
                <flux:button variant="outline" size="sm" class="w-full" href="#">Manage Portfolio</flux:button>
            </flux:card>

            <!-- Loan Card -->
            <flux:card class="p-6 space-y-4 border-zinc-200 dark:border-zinc-800">
                <div class="flex justify-between items-start">
                    <flux:heading size="sm" class="uppercase tracking-widest font-bold text-zinc-500">Loans</flux:heading>
                    <flux:icon.banknotes class="size-5 text-orange-500" />
                </div>
                @if($this->activeLoans->count() > 0)
                    <div>
                        <div class="text-2xl font-bold tracking-tight text-orange-600">{{ Number::currency($this->loanBalance) }}</div>
                        <flux:text class="text-xs text-zinc-400">Total Outstanding</flux:text>
                    </div>
                    <flux:button variant="outline" size="sm" class="w-full" href="{{ route('loan.index') }}" wire:navigate>View Details</flux:button>
                @else
                    <div class="py-2">
                        <flux:text class="text-sm">No active loans. Need extra funds?</flux:text>
                    </div>
                    <flux:button variant="primary" size="sm" class="w-full" href="{{ route('loan.apply') }}" wire:navigate>Apply Now</flux:button>
                @endif
            </flux:card>
        </div>
    </div>
</div>
