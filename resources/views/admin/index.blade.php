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
        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-[--radius-field] bg-neutral-100 dark:bg-neutral-900/20 text-neutral-600 dark:text-neutral-400">
                    <x-ui.icon name="currency-dollar" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ number_format($totalDividends) }}</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Dividends</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-[--radius-field] bg-secondary/10 dark:bg-secondary/20 text-secondary">
                    <x-ui.icon name="arrow-path" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ number_format($totalTransactions) }}</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Transactions</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-[--radius-field] bg-success/10 dark:bg-success/20 text-success">
                    <x-ui.icon name="check-circle" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ number_format($successfulTransactions) }}</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Successful Transactions</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Recent Users</h3>
                <x-ui.button tag="a" href="{{ route('admin.users.index') }}" variant="ghost" size="sm">
                    View All
                </x-ui.button>
            </div>
            <div class="divide-y divide-neutral-100 dark:divide-neutral-700">
                @forelse($recentUsers as $user)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                        <div>
                            <p class="font-bold text-neutral-900 dark:text-white text-xs">{{ $user->name }}</p>
                            <p class="text-[10px] text-neutral-500 dark:text-neutral-400">{{ $user->email }}</p>
                        </div>
                        <p class="text-[10px] text-neutral-400">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400 italic text-xs">
                        No users yet
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Loans -->
        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Recent Loans</h3>
                <x-ui.button tag="a" href="{{ route('admin.loans.index') }}" variant="ghost" size="sm">
                    View All
                </x-ui.button>
            </div>
            <div class="divide-y divide-neutral-100 dark:divide-neutral-700">
                @forelse($recentLoans as $loan)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                        <div>
                            <p class="font-bold text-neutral-900 dark:text-white text-xs">{{ $loan->user->name }}</p>
                            <p class="text-[10px] text-neutral-500 dark:text-neutral-400">₦{{ number_format($loan->amount, 2) }}</p>
                        </div>
                        <x-ui.badge :color="$loan->status->getColor()" class="text-[10px]">
                            {{ $loan->status->getLabel() }}
                        </x-ui.badge>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400 italic text-xs">
                        No loans yet
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Recent Transactions</h3>
            <x-ui.button tag="a" href="{{ route('admin.transactions.index') }}" variant="ghost" size="sm">
                View All
            </x-ui.button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr class="border-b border-neutral-100 dark:border-neutral-700">
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4 text-center">Type</th>
                        <th class="px-6 py-4 text-center">Amount</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($recentTransactions as $transaction)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white truncate max-w-[150px]">
                                {{ $transaction->user->name }}
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400 text-center">
                                {{ $transaction->type->getLabel() }}
                            </td>
                            <td class="px-6 py-4 text-neutral-900 dark:text-white font-bold text-center">
                                ₦{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <x-ui.badge :color="$transaction->status->getColor()" class="text-[10px]">
                                    {{ $transaction->status->getLabel() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400 text-right">
                                {{ $transaction->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400 italic">
                                No transactions yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
