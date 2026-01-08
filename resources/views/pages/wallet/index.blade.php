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

    <!-- Balance Card with Actions -->
    <div class="bg-primary rounded-2xl shadow-lg p-8 text-primary-fg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <!-- Balance Section -->
            <div>
                <p class="text-primary-fg/80 text-sm font-medium mb-2">Available Balance</p>
                <div class="flex items-baseline gap-3">
                    <span class="text-5xl font-bold">
                        {{ Number::currency(auth()->user()->wallet_balance) }}
                    </span>
                </div>
                <p class="text-primary-fg/80 text-sm mt-3">
                    <span class="inline-flex items-center gap-1">
                        <x-ui.icon name="shield-check" class="w-4 h-4" />
                        Secured & Encrypted
                    </span>
                </p>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <x-ui.button 
                    tag="a" 
                    href="{{ route('wallet.fund') }}" 
                    variant="outline"
                    icon="plus-circle"
                    size="lg"
                    class="justify-center shadow-lg hover:shadow-xl transition-shadow border-primary-fg/20 hover:bg-primary-fg/10 text-primary-fg"
                >
                    Fund Wallet
                </x-ui.button>
            </div>
        </div>
    </div>

    <!-- Main Action Cards -->
    <div class="grid gap-4 md:grid-cols-2">
        <!-- Transfer Fund Card -->
        <a href="{{ route('wallet.transfer') }}" class="group block">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-100 dark:border-gray-700 hover:border-primary dark:hover:border-primary transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors">
                                <x-ui.icon name="arrow-up-tray" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transfer Funds</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Send money to other users instantly using their phone number
                        </p>
                    </div>
                    <x-ui.icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors mt-1" />
                </div>
            </div>
        </a>

        <!-- Withdraw Card -->
        <a href="{{ route('wallet.withdraw') }}" class="group block">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-100 dark:border-gray-700 hover:border-primary dark:hover:border-primary transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg group-hover:bg-green-100 dark:group-hover:bg-green-900/30 transition-colors">
                                <x-ui.icon name="arrow-down-tray" class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Withdraw Funds</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Withdraw money to your bank account securely
                        </p>
                    </div>
                    <x-ui.icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors mt-1" />
                </div>
            </div>
        </a>
    </div>

    <!-- Wallet Info & Stats -->
    <div class="grid gap-6 md:grid-cols-3">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <x-ui.icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h4>
            </div>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">Active</p>
        </div>

        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <x-ui.icon name="currency-dollar" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Currency</h4>
            </div>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">NGN</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nigerian Naira</p>
        </div>

        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <x-ui.icon name="clock" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Activity</h4>
            </div>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">Today</p>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
            <x-ui.button tag="a" href="#" variant="ghost" size="sm">
                View All
            </x-ui.button>
        </div>
        <div class="p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                <x-ui.icon name="inbox" class="w-8 h-8 text-gray-400" />
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">No transactions yet</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Your transaction history will appear here</p>
        </div>
    </div>
</div>
