<?php

use App\Enums\LoanStatus;
use App\Enums\ShareStatus;
use App\Enums\Transaction\Status as TransactionStatus;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Share;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $user = auth()->user();

        // User's loan statistics
        $totalLoans = Loan::where('user_id', $user->id)->count();
        $activeLoans = Loan::where('user_id', $user->id)
            ->where('status', LoanStatus::ACTIVE)
            ->count();
        $totalBorrowed = Loan::where('user_id', $user->id)->sum('amount');
        $totalRepaid = LoanPayment::whereHas('loan', fn($q) => $q->where('user_id', $user->id))->sum('amount');

        // Loan repayment history (last 12 months)
        $loanRepaymentHistory = LoanPayment::whereHas('loan', fn ($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', now()->subMonths(12))
            ->get()
            ->groupBy(fn ($payment) => $payment->created_at->format('Y-m'))
            ->map(fn ($group, $month) => [
                'month' => $month,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ])
            ->values()
            ->sortBy('month');

        // Share portfolio
        $totalShares = Share::where('holder_type', $user->getMorphClass())
            ->where('holder_id', $user->id)
            ->where('status', ShareStatus::APPROVED)
            ->sum('quantity');
        $shareSettings = app(\App\Settings\ShareSettings::class);
        $shareValue = $totalShares * ($shareSettings->share_price ?? 0);

        // Share purchase history (last 12 months)
        $sharePurchaseHistory = Share::where('holder_type', $user->getMorphClass())
            ->where('holder_id', $user->id)
            ->where('created_at', '>=', now()->subMonths(12))
            ->get()
            ->groupBy(fn ($share) => $share->created_at->format('Y-m'))
            ->map(fn ($group, $month) => [
                'month' => $month,
                'quantity' => $group->sum('quantity'),
                'amount' => $group->sum(fn ($s) => $s->quantity * ($shareSettings->share_price ?? 0)),
            ])
            ->values()
            ->sortBy('month');

        // Transaction statistics (last 30 days)
        $transactionsByType = Transaction::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->where('status', TransactionStatus::Success)
            ->get()
            ->groupBy(fn ($transaction) => $transaction->type->getLabel())
            ->map(fn ($group) => [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ]);

        // Monthly spending trend (last 6 months)
        $monthlySpending = Transaction::where('user_id', $user->id)
            ->where('status', TransactionStatus::Success)
            ->where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn ($transaction) => $transaction->created_at->format('Y-m'))
            ->map(fn ($group, $month) => [
                'month' => $month,
                'total' => $group->sum('amount'),
            ])
            ->values()
            ->sortBy('month');

        // Wallet balance history (if tracked)
        $walletBalance = $user->wallet?->balance ?? 0;

        return $this->view()->with([
            'totalLoans' => $totalLoans,
            'activeLoans' => $activeLoans,
            'totalBorrowed' => $totalBorrowed,
            'totalRepaid' => $totalRepaid,
            'loanRepaymentHistory' => $loanRepaymentHistory,
            'totalShares' => $totalShares,
            'shareValue' => $shareValue,
            'sharePurchaseHistory' => $sharePurchaseHistory,
            'transactionsByType' => $transactionsByType,
            'monthlySpending' => $monthlySpending,
            'walletBalance' => $walletBalance,
        ])->layout('layouts::app');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="My Analytics" 
        description="Track your financial activity and performance"
    />

    <!-- Financial Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Wallet Balance -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                    <x-ui.icon name="wallet" class="w-6 h-6" />
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">₦{{ number_format($walletBalance, 2) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Wallet Balance</p>
        </div>

        <!-- Total Shares -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                    <x-ui.icon name="chart-pie" class="w-6 h-6" />
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalShares) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Shares (₦{{ number_format($shareValue, 2) }})</p>
        </div>

        <!-- Total Borrowed -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">
                    <x-ui.icon name="banknotes" class="w-6 h-6" />
                </div>
                <x-ui.badge color="warning">{{ $activeLoans }} Active</x-ui.badge>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">₦{{ number_format($totalBorrowed, 2) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Borrowed</p>
        </div>

        <!-- Total Repaid -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                    <x-ui.icon name="check-circle" class="w-6 h-6" />
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">₦{{ number_format($totalRepaid, 2) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Repaid</p>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Loan Repayment History -->
        <x-ui.chart title="Loan Repayment History" description="Your loan repayments over the last 12 months">
            <canvas id="loanRepaymentChart"></canvas>
        </x-ui.chart>

        <!-- Share Portfolio Growth -->
        <x-ui.chart title="Share Portfolio Growth" description="Your share purchases over time">
            <canvas id="sharePortfolioChart"></canvas>
        </x-ui.chart>

        <!-- Transaction Breakdown -->
        <x-ui.chart title="Transaction Breakdown" description="Your transactions by type (Last 30 days)">
            <canvas id="transactionBreakdownChart"></canvas>
        </x-ui.chart>

        <!-- Monthly Spending Trend -->
        <x-ui.chart title="Monthly Spending" description="Your spending trend over the last 6 months">
            <canvas id="monthlySpendingChart"></canvas>
        </x-ui.chart>
    </div>

    <!-- Transaction Details Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Transaction Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Count</th>
                        <th class="px-6 py-4">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($transactionsByType as $type => $data)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $type }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $data['count'] }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">₦{{ number_format($data['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No transactions in the last 30 days
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js default configuration
    Chart.defaults.color = document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280';
    Chart.defaults.borderColor = document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb';

    // Loan Repayment History Chart
    new Chart(document.getElementById('loanRepaymentChart'), {
        type: 'bar',
        data: {
            labels: @json($loanRepaymentHistory->pluck('month')),
            datasets: [{
                label: 'Repayment Amount (₦)',
                data: @json($loanRepaymentHistory->pluck('total')),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Share Portfolio Chart
    new Chart(document.getElementById('sharePortfolioChart'), {
        type: 'line',
        data: {
            labels: @json($sharePurchaseHistory->pluck('month')),
            datasets: [{
                label: 'Shares Purchased',
                data: @json($sharePurchaseHistory->pluck('quantity')),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Transaction Breakdown Chart
    new Chart(document.getElementById('transactionBreakdownChart'), {
        type: 'doughnut',
        data: {
            labels: @json($transactionsByType->keys()),
            datasets: [{
                data: @json($transactionsByType->pluck('total')),
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(168, 85, 247)',
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(239, 68, 68)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₦' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Monthly Spending Chart
    new Chart(document.getElementById('monthlySpendingChart'), {
        type: 'line',
        data: {
            labels: @json($monthlySpending->pluck('month')),
            datasets: [{
                label: 'Total Spending (₦)',
                data: @json($monthlySpending->pluck('total')),
                borderColor: 'rgb(168, 85, 247)',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Update charts on theme change
    document.addEventListener('theme-changed', function(e) {
        const isDark = e.detail.theme === 'dark';
        Chart.defaults.color = isDark ? '#9ca3af' : '#6b7280';
        Chart.defaults.borderColor = isDark ? '#374151' : '#e5e7eb';
        
        Chart.instances.forEach(chart => chart.update());
    });
</script>
@endpush
