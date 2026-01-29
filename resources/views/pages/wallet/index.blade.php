<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    //
}; ?>

<div class="max-w-6xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="My Wallet" 
        description="Manage your wallet balance and transactions"
    />

    <x-wallet.card :balance="auth()->user()->wallet_balance" variant="full">
        <div class="flex flex-col sm:flex-row gap-3">
            <x-ui.button 
                tag="a" 
                href="{{ route('wallet.fund') }}" 
                variant="outline"
                icon="plus-circle"
                size="lg"
                class="justify-center shadow-lg hover:shadow-xl transition-shadow border-white/20 hover:bg-white/10 text-white font-bold"
            >
                Fund Wallet
            </x-ui.button>
        </div>
    </x-wallet.card>

    <!-- Main Action Cards -->
    <div class="grid gap-4 md:grid-cols-2">
        <!-- Transfer Fund Card -->
        <a href="{{ route('wallet.transfer') }}" class="group block">
            <div class="p-6 bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 hover:border-primary transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-primary/10 rounded-[--radius-field] group-hover:bg-primary/20 transition-colors">
                                <x-ui.icon name="arrow-up-tray" variant="solid" class="size-6 text-primary" />
                            </div>
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Transfer Funds</h3>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Send money to other users instantly using their phone number
                        </p>
                    </div>
                    <x-ui.icon name="chevron-right" class="size-5 text-neutral-400 group-hover:text-primary transition-colors mt-1" />
                </div>
            </div>
        </a>

        <!-- Withdraw Card -->
        <a href="{{ route('wallet.withdraw') }}" class="group block">
            <div class="p-6 bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 hover:border-primary transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-secondary/10 rounded-[--radius-field] group-hover:bg-secondary/20 transition-colors">
                                <x-ui.icon name="arrow-down-tray" variant="solid" class="size-6 text-secondary" />
                            </div>
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Withdraw Funds</h3>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Withdraw money to your bank account securely
                        </p>
                    </div>
                    <x-ui.icon name="chevron-right" class="size-5 text-neutral-400 group-hover:text-primary transition-colors mt-1" />
                </div>
            </div>
        </a>
    </div>

    <!-- Wallet Info & Stats -->
    <div class="grid gap-6 md:grid-cols-3">
        <div class="p-6 bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-success/10 rounded-[--radius-field]">
                    <x-ui.icon name="check-circle" variant="solid" class="size-5 text-success" />
                </div>
                <h4 class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Status</h4>
            </div>
            <p class="text-base font-bold text-neutral-900 dark:text-white">Active</p>
        </div>

        <div class="p-6 bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-info/10 rounded-[--radius-field]">
                    <x-ui.icon name="currency-dollar" variant="solid" class="size-5 text-info" />
                </div>
                <h4 class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Currency</h4>
            </div>
            <p class="text-base font-bold text-neutral-900 dark:text-white">NGN</p>
            <p class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-1 uppercase tracking-wider font-bold">Nigerian Naira</p>
        </div>

        <div class="p-6 bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-accent/10 rounded-[--radius-field]">
                    <x-ui.icon name="clock" variant="solid" class="size-5 text-accent" />
                </div>
                <h4 class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Last Activity</h4>
            </div>
            <p class="text-base font-bold text-neutral-900 dark:text-white">Today</p>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="p-6 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
            <h3 class="font-bold text-neutral-900 dark:text-white">Recent Transactions</h3>
            <x-ui.button tag="a" href="#" variant="ghost" size="sm" class="text-neutral-500 font-bold hover:text-neutral-900 dark:hover:text-white">
                View All
            </x-ui.button>
        </div>
        <div class="p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-neutral-50 dark:bg-neutral-700/50 rounded-full mb-4">
                <x-ui.icon name="inbox" class="size-8 text-neutral-300 dark:text-neutral-500" />
            </div>
            <p class="text-neutral-900 dark:text-white font-bold">No transactions yet</p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Your transaction history will appear here</p>
        </div>
    </div>
</div>
