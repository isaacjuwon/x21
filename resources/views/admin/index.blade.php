<?php

use App\Enums\LoanStatus;
use App\Enums\ShareStatus;
use App\Enums\Transaction\Status as TransactionStatus;
use App\Models\Dividend;
use App\Models\Loan;
use App\Models\Share;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view([
            'totalUsers' => User::count(),
            'totalLoans' => Loan::count(),
            'activeLoans' => Loan::where('status', LoanStatus::ACTIVE)->count(),
            'pendingLoans' => Loan::where('status', LoanStatus::PENDING)->count(),
            'totalShares' => Share::count(),
            'approvedShares' => Share::where('status', ShareStatus::APPROVED)->count(),
            'pendingShares' => Share::where('status', ShareStatus::PENDING)->count(),
            'totalDividends' => Dividend::count(),
            'totalTransactions' => Transaction::count(),
            'successfulTransactions' => Transaction::where('status', TransactionStatus::Success)->count(),
            'totalWalletBalance' => Wallet::sum('balance'),
            'recentUsers' => User::latest()->take(5)->get(),
            'recentLoans' => Loan::with('user')->latest()->take(5)->get(),
            'recentTransactions' => Transaction::with('user')->latest()->take(5)->get(),
        ])->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Admin Dashboard" 
        description="Overview of platform statistics and recent activity"
    />

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                    <x-ui.icon name="users" class="w-6 h-6" />
                </div>
                <x-ui.badge color="neutral">Total</x-ui.badge>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Registered Users</p>
        </div>

        <!-- Total Loans -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">
                    <x-ui.icon name="banknotes" class="w-6 h-6" />
                </div>
                <x-ui.badge color="warning">{{ $pendingLoans }} Pending</x-ui.badge>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalLoans) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Loans ({{ $activeLoans }} Active)</p>
        </div>

        <!-- Total Shares -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                    <x-ui.icon name="chart-pie" class="w-6 h-6" />
                </div>
                <x-ui.badge color="warning">{{ $pendingShares }} Pending</x-ui.badge>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalShares) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Shares ({{ $approvedShares }} Approved)</p>
        </div>

        <!-- Total Wallet Balance -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400">
                    <x-ui.icon name="wallet" class="w-6 h-6" />
                </div>
                <x-ui.badge color="success">Active</x-ui.badge>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">₦{{ number_format($totalWalletBalance, 2) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Wallet Balance</p>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                    <x-ui.icon name="currency-dollar" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totalDividends) }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Dividends</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400">
                    <x-ui.icon name="arrow-path" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totalTransactions) }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Transactions</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                    <x-ui.icon name="check-circle" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($successfulTransactions) }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Successful Transactions</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Users</h3>
                <x-ui.button tag="a" href="{{ route('admin.users.index') }}" variant="ghost" size="sm">
                    View All
                </x-ui.button>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recentUsers as $user)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                        </div>
                        <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No users yet
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Loans -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Loans</h3>
                <x-ui.button tag="a" href="{{ route('admin.loans.index') }}" variant="ghost" size="sm">
                    View All
                </x-ui.button>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recentLoans as $loan)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $loan->user->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">₦{{ number_format($loan->amount, 2) }}</p>
                        </div>
                        <x-ui.badge :color="$loan->status->getColor()">
                            {{ $loan->status->getLabel() }}
                        </x-ui.badge>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No loans yet
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Transactions</h3>
            <x-ui.button tag="a" href="{{ route('admin.transactions.index') }}" variant="ghost" size="sm">
                View All
            </x-ui.button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentTransactions as $transaction)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $transaction->user->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $transaction->type->getLabel() }}
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                ₦{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$transaction->status->getColor()">
                                    {{ $transaction->status->getLabel() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $transaction->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No transactions yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
