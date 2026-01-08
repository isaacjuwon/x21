<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function walletBalance()
    {
        return auth()->user()->wallet_balance;
    }

    #[Computed]
    public function sharesValue()
    {
        return auth()->user()->getSharesValue();
    }

    #[Computed]
    public function totalShares()
    {
        return auth()->user()->shares()->where('quantity', '>', 0)->sum('quantity');
    }

    #[Computed]
    public function activeLoan()
    {
        return auth()->user()->activeLoan();
    }

    #[Computed]
    public function loanEligibility()
    {
        return auth()->user()->getLoanEligibilityAmount();
    }

    public function render()
    {
        return $this->view()
            ->title('Dashboard')
            ->layout('layouts::app');
    }
};
?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">Dashboard</h1>
                    <p class="text-zinc-600 dark:text-zinc-400">Welcome back, <span class="font-semibold">{{ auth()->user()->name }}</span></p>
                </div>
                
                @role('admin')
                    <x-ui.button 
                        tag="a"
                        href="{{ route('admin.dashboard') }}"
                        variant="outline"
                        size="sm"
                        icon="cog-6-tooth"
                        wire:navigate
                    >
                        Admin Dashboard
                    </x-ui.button>
                @endrole
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Wallet Card -->
            <!-- Wallet Card -->
            <div class="relative overflow-hidden bg-primary rounded-2xl shadow-lg p-6 text-primary-fg">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-primary-fg/80 text-sm font-medium">Wallet Balance</p>
                        <div class="p-2 bg-primary-fg/20 rounded-lg backdrop-blur-sm">
                            <x-ui.icon name="wallet" class="w-5 h-5" />
                        </div>
                    </div>
                    <p class="text-4xl font-bold mb-1">{{ Number::currency(auth()->user()->wallet_balance) }}</p>
                    <p class="text-primary-fg/80 text-sm mb-6">Available funds</p>
                    <x-ui.button 
                        size="sm" 
                        variant="outline"
                        class="w-full justify-center shadow-md hover:shadow-lg transition-shadow border-primary-fg/20 hover:bg-primary-fg/10 text-primary-fg" 
                        wire:navigate 
                        href="/wallet"
                    >
                        Manage Wallet
                    </x-ui.button>
                </div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-fg/5 rounded-full -mr-16 -mt-16"></div>
            </div>

            <!-- Shares Card -->
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-emerald-700 dark:from-emerald-700 dark:to-emerald-800 rounded-2xl shadow-lg p-6 text-white">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-emerald-100 text-sm font-medium">Shares Portfolio</p>
                        <div class="p-2 bg-white/10 rounded-lg backdrop-blur-sm">
                            <x-ui.icon name="chart-bar" class="w-5 h-5" />
                        </div>
                    </div>
                    <p class="text-4xl font-bold mb-1">{{ Number::currency($this->sharesValue) }}</p>
                    <p class="text-emerald-100 text-sm mb-6">{{ $this->totalShares }} shares owned</p>
                    <x-ui.button 
                        size="sm" 
                        variant="outline"

                        class="w-full justify-center shadow-md hover:shadow-lg transition-shadow border-primary-fg/20 hover:bg-primary-fg/40 text-primary-fg" 
                        wire:navigate 
                        href="/shares"
                    >
                        View Portfolio
                    </x-ui.button>
                </div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
            </div>

            <!-- Loan Card -->
            <div class="relative overflow-hidden bg-gradient-to-br from-amber-600 to-amber-700 dark:from-amber-700 dark:to-amber-800 rounded-2xl shadow-lg p-6 text-white">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-amber-100 text-sm font-medium">
                            @if($this->activeLoan)
                                Active Loan
                            @else
                                Loan Eligibility
                            @endif
                        </p>
                        <div class="p-2 bg-white/10 rounded-lg backdrop-blur-sm">
                            <x-ui.icon name="banknotes" class="w-5 h-5" />
                        </div>
                    </div>
                    @if($this->activeLoan)
                        <p class="text-4xl font-bold mb-1">{{ Number::currency($this->activeLoan->balance_remaining) }}</p>
                        <p class="text-amber-100 text-sm mb-6">{{ number_format($this->activeLoan->progress_percentage, 1) }}% paid</p>
                        <x-ui.button 
                            size="sm" 
                            variant="outline"
                            class="w-full justify-center shadow-md hover:shadow-lg transition-shadow border-primary-fg/20 " 
                            wire:navigate 
                            href="/loans/{{ $this->activeLoan->id }}"
                        >
                            View Loan
                        </x-ui.button>
                    @else
                        <p class="text-4xl font-bold mb-1">{{ Number::currency($this->loanEligibility) }}</p>
                        <p class="text-amber-100 text-sm mb-6">Available to borrow</p>
                        <x-ui.button 
                            size="sm" 
                            variant="outline"
                            class="w-full justify-center shadow-md hover:shadow-lg transition-shadow border-primary-fg/20 hover:bg-primary-fg/40 text-primary-fg" 
                            wire:navigate 
                            href="/loans/apply"
                        >
                            Apply Now
                        </x-ui.button>
                    @endif
                </div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Quick Actions</h2>
            
            <div class="grid grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Airtime -->
                <a wire:navigate href="{{ route('airtime') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all">
                    <div class="w-14 h-14 bg-purple-50 dark:bg-purple-900/20 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="device-phone-mobile" class="w-7 h-7 text-purple-600 dark:text-purple-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 text-center uppercase tracking-wider">Airtime</span>
                </a>

                <!-- Education -->
                <a wire:navigate href="{{ route('education') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all">
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="academic-cap" class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 text-center uppercase tracking-wider">Education</span>
                </a>

                <!-- Electricity -->
                <a wire:navigate href="{{ route('electricity') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all">
                    <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="bolt" class="w-7 h-7 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 text-center uppercase tracking-wider">Electricity</span>
                </a>

                <!-- Cable -->
                <a wire:navigate href="{{ route('cable') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all">
                    <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="tv" class="w-7 h-7 text-blue-600 dark:text-blue-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 text-center uppercase tracking-wider">Cable TV</span>
                </a>

                <!-- Data -->
                <a wire:navigate href="{{ route('data') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all">
                    <div class="w-14 h-14 bg-sky-50 dark:bg-sky-900/20 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="signal" class="w-7 h-7 text-sky-600 dark:text-sky-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 text-center uppercase tracking-wider">Internet Data</span>
                </a>

                <!-- Others -->
                <a wire:navigate href="{{ route('wallet.index') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all">
                    <div class="w-14 h-14 bg-zinc-50 dark:bg-zinc-900/20 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="squares-2x2" class="w-7 h-7 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 text-center uppercase tracking-wider">Others</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Loans -->
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Recent Loans</h3>
                    <x-ui.button size="sm" variant="ghost" wire:navigate href="/loans">
                        View All
                    </x-ui.button>
                </div>

                @php
                    $recentLoans = auth()->user()->loans()->latest()->take(3)->get();
                @endphp

                @if($recentLoans->count() > 0)
                    <div class="p-6 space-y-3">
                        @foreach($recentLoans as $loan)
                            <a wire:navigate href="/loans/{{ $loan->id }}" class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors group">
                                <div class="flex items-center gap-4">
                                    <div class="p-2 bg-amber-100 dark:bg-amber-900/20 rounded-lg">
                                        <x-ui.icon name="banknotes" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ Number::currency($loan->amount) }}</p>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $loan->applied_at?->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <x-ui.badge :color="$loan->status_badge">
                                    {{$loan->status }}
                                </x-ui.badge>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full mb-4">
                            <x-ui.icon name="banknotes" class="w-8 h-8 text-zinc-400" />
                        </div>
                        <p class="text-zinc-500 dark:text-zinc-400 font-medium mb-3">No loans yet</p>
                        <x-ui.button size="sm" wire:navigate href="/loans/apply">
                            Apply for Loan
                        </x-ui.button>
                    </div>
                @endif
            </div>

            <!-- Shares Summary -->
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Shares Summary</h3>
                    <x-ui.button size="sm" variant="ghost" wire:navigate href="/shares">
                        View All
                    </x-ui.button>
                </div>

                @php
                    $userShares = auth()->user()->shares()->where('quantity', '>', 0)->get();
                @endphp

                @if($userShares->count() > 0)
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-1">Total Shares</p>
                                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->totalShares }}</p>
                            </div>
                            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-1">Total Value</p>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ Number::currency($this->sharesValue) }}</p>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 space-y-3">
                            @foreach($userShares as $share)
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $share->currency }}</span>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $share->quantity }} shares</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full mb-4">
                            <x-ui.icon name="chart-bar" class="w-8 h-8 text-zinc-400" />
                        </div>
                        <p class="text-zinc-500 dark:text-zinc-400 font-medium mb-3">No shares yet</p>
                        <x-ui.button size="sm" wire:navigate href="/shares/buy">
                            Buy Shares
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>