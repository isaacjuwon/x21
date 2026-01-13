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

<div class="min-h-screen bg-background text-foreground">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground mb-2">Dashboard</h1>
                    <p class="text-foreground-content">Welcome back, <span class="font-semibold text-foreground">{{ auth()->user()->name }}</span></p>
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
            <x-wallet.card :balance="auth()->user()->wallet_balance" />

            <!-- Shares Card -->
            <div class="relative overflow-hidden bg-background-content rounded-2xl shadow-sm border border-border p-6">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-foreground-content text-sm font-medium">Shares Portfolio</p>
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <x-ui.icon name="chart-bar" variant="solid" class="size-5 text-primary" />
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-foreground mb-1 truncate" title="{{ Number::currency($this->sharesValue) }}">{{ Number::currency($this->sharesValue) }}</p>
                    <p class="text-foreground-content text-sm mb-6">{{ $this->totalShares }} shares owned</p>
                    <x-ui.button 
                        size="sm" 
                        variant="primary"
                        class="w-full justify-center" 
                        wire:navigate 
                        href="/shares"
                    >
                        View Portfolio
                    </x-ui.button>
                </div>
            </div>

            <!-- Loan Card -->
            <div class="relative overflow-hidden bg-background-content rounded-2xl shadow-sm border border-border p-6">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-foreground-content text-sm font-medium">
                            @if($this->activeLoan)
                                Active Loan
                            @else
                                Loan Eligibility
                            @endif
                        </p>
                        <div class="p-2 bg-accent/10 rounded-lg">
                            <x-ui.icon name="banknotes" variant="solid" class="size-5 text-accent" />
                        </div>
                    </div>
                    @if($this->activeLoan)
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-foreground mb-1 truncate" title="{{ Number::currency($this->activeLoan->balance_remaining) }}">{{ Number::currency($this->activeLoan->balance_remaining) }}</p>
                        <p class="text-foreground-content text-sm mb-6">{{ number_format($this->activeLoan->progress_percentage, 1) }}% paid</p>
                        <x-ui.button 
                            size="sm" 
                            variant="primary"
                            class="w-full justify-center" 
                            wire:navigate 
                            href="/loans/{{ $this->activeLoan->id }}"
                        >
                            View Loan
                        </x-ui.button>
                    @else
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-foreground mb-1 truncate" title="{{ Number::currency($this->loanEligibility) }}">{{ Number::currency($this->loanEligibility) }}</p>
                        <p class="text-foreground-content text-sm mb-6">Available to borrow</p>
                        <x-ui.button 
                            size="sm" 
                            variant="outline"
                            class="w-full justify-center" 
                            wire:navigate 
                            href="/loans/apply"
                        >
                            Apply Now
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-background-content rounded-2xl shadow-sm border border-border p-6">
            <h2 class="text-xl font-bold text-foreground mb-6">Quick Actions</h2>
            
            <div class="grid grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Airtime -->
                <a wire:navigate href="{{ route('airtime') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-background transition-all">
                    <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="device-phone-mobile" variant="solid" class="w-7 h-7 text-primary" />
                    </div>
                    <span class="text-xs font-medium text-foreground-content text-center uppercase tracking-wider">Airtime</span>
                </a>

                <!-- Education -->
                <a wire:navigate href="{{ route('education') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-background transition-all">
                    <div class="w-14 h-14 bg-success/10 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="academic-cap" variant="solid" class="w-7 h-7 text-success" />
                    </div>
                    <span class="text-xs font-medium text-foreground-content text-center uppercase tracking-wider">Education</span>
                </a>

                <!-- Electricity -->
                <a wire:navigate href="{{ route('electricity') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-background transition-all">
                    <div class="w-14 h-14 bg-warning/10 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="bolt" variant="solid" class="w-7 h-7 text-warning" />
                    </div>
                    <span class="text-xs font-medium text-foreground-content text-center uppercase tracking-wider">Electricity</span>
                </a>

                <!-- Cable -->
                <a wire:navigate href="{{ route('cable') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-background transition-all">
                    <div class="w-14 h-14 bg-info/10 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="tv" variant="solid" class="w-7 h-7 text-info" />
                    </div>
                    <span class="text-xs font-medium text-foreground-content text-center uppercase tracking-wider">Cable TV</span>
                </a>

                <!-- Data -->
                <a wire:navigate href="{{ route('data') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-background transition-all">
                    <div class="w-14 h-14 bg-info/10 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="signal" variant="solid" class="w-7 h-7 text-info" />
                    </div>
                    <span class="text-xs font-medium text-foreground-content text-center uppercase tracking-wider">Internet Data</span>
                </a>

                <!-- Others -->
                <a wire:navigate href="{{ route('wallet.index') }}" class="group flex flex-col items-center p-4 rounded-xl hover:bg-background transition-all">
                    <div class="w-14 h-14 bg-foreground-content/10 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <x-ui.icon name="squares-2x2" variant="solid" class="w-7 h-7 text-foreground-content" />
                    </div>
                    <span class="text-xs font-medium text-foreground-content text-center uppercase tracking-wider">Others</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Loans -->
            <div class="bg-background-content rounded-2xl shadow-sm border border-border overflow-hidden">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-foreground">Recent Loans</h3>
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
                            <a wire:navigate href="/loans/{{ $loan->id }}" class="flex items-center justify-between p-4 bg-background rounded-xl hover:bg-background-content transition-colors group">
                                <div class="flex items-center gap-4">
                                    <div class="p-2 bg-warning/10 rounded-lg">
                                        <x-ui.icon name="banknotes" variant="solid" class="w-5 h-5 text-warning" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-foreground truncate max-w-[120px]" title="{{ Number::currency($loan->amount) }}">{{ Number::currency($loan->amount) }}</p>
                                        <p class="text-sm text-foreground-content">{{ $loan->applied_at?->format('M d, Y') }}</p>
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
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-background rounded-full mb-4">
                            <x-ui.icon name="banknotes" class="size-8 text-foreground-content" />
                        </div>
                        <p class="text-foreground-content font-medium mb-3">No loans yet</p>
                        <x-ui.button size="sm" wire:navigate href="/loans/apply">
                            Apply for Loan
                        </x-ui.button>
                    </div>
                @endif
            </div>

            <!-- Shares Summary -->
            <div class="bg-background-content rounded-2xl shadow-sm border border-border overflow-hidden">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-foreground">Shares Summary</h3>
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
                            <div class="text-center p-4 bg-success/10 rounded-xl">
                                <p class="text-sm text-foreground-content mb-1">Total Shares</p>
                                <p class="text-2xl sm:text-3xl font-bold text-success truncate" title="{{ $this->totalShares }}">{{ $this->totalShares }}</p>
                            </div>
                            <div class="text-center p-4 bg-info/10 rounded-xl">
                                <p class="text-sm text-foreground-content mb-1">Total Value</p>
                                <p class="text-2xl sm:text-3xl font-bold text-info truncate" title="{{ Number::currency($this->sharesValue) }}">{{ Number::currency($this->sharesValue) }}</p>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-border space-y-3">
                            @foreach($userShares as $share)
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm font-medium text-foreground">{{ $share->currency }}</span>
                                    <span class="text-sm text-foreground-content">{{ $share->quantity }} shares</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-background rounded-full mb-4">
                            <x-ui.icon name="chart-bar" class="size-8 text-foreground-content" />
                        </div>
                        <p class="text-foreground-content font-medium mb-3">No shares yet</p>
                        <x-ui.button size="sm" wire:navigate href="/shares/buy">
                            Buy Shares
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>