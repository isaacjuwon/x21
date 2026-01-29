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
        <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-[--radius-field] bg-success/10 text-success">
                    <x-ui.icon name="wallet" class="w-6 h-6" />
                </div>
            </div>
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ Number::currency($walletBalance, 'NGN') }}</h3>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mt-1">Wallet Balance</p>
        </x-ui.card>

        <!-- Total Shares -->
        <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-[--radius-field] bg-primary/10 text-primary">
                    <x-ui.icon name="chart-pie" class="w-6 h-6" />
                </div>
            </div>
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ number_format($totalShares) }}</h3>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mt-1">Total Shares ({{ Number::currency($shareValue, 'NGN') }})</p>
        </x-ui.card>

        <!-- Total Borrowed -->
        <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-[--radius-field] bg-error/10 text-error">
                    <x-ui.icon name="banknotes" class="w-6 h-6" />
                </div>
                <x-ui.badge color="warning" class="text-[10px] font-bold uppercase tracking-widest">{{ $activeLoans }} Active</x-ui.badge>
            </div>
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ Number::currency($totalBorrowed, 'NGN') }}</h3>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mt-1">Total Borrowed</p>
        </x-ui.card>

        <!-- Total Repaid -->
        <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-[--radius-field] bg-purple-500/10 text-purple-600">
                    <x-ui.icon name="check-circle" class="w-6 h-6" />
                </div>
            </div>
            <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ Number::currency($totalRepaid, 'NGN') }}</h3>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mt-1">Total Repaid</p>
        </x-ui.card>
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
    <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none overflow-hidden">
        <div class="px-6 py-5 border-b border-neutral-100 dark:border-neutral-700">
            <h3 class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-widest">Recent Transaction Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-900/50 text-neutral-400 font-bold">
                    <tr>
                        <th class="px-6 py-4 uppercase tracking-widest">Type</th>
                        <th class="px-6 py-4 uppercase tracking-widest">Count</th>
                        <th class="px-6 py-4 uppercase tracking-widest">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($transactionsByType as $type => $data)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">{{ $type }}</td>
                            <td class="px-6 py-4 text-neutral-500 font-bold">{{ $data['count'] }}</td>
                            <td class="px-6 py-4 text-neutral-900 dark:text-white font-bold">{{ Number::currency($data['total'], 'NGN') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-neutral-400 font-bold uppercase tracking-widest">
                                No transactions in the last 30 days
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
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
