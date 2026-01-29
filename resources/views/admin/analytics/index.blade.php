<?php

use App\Enums\ShareStatus;
use App\Enums\Transaction\Status as TransactionStatus;
use App\Models\AirtimePlan;
use App\Models\CablePlan;
use App\Models\DataPlan;
use App\Models\EducationPlan;
use App\Models\ElectricityPlan;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Share;
use App\Models\Transaction;
use App\Models\User;
use App\Settings\ShareSettings;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $driver = DB::getDriverName();
        $dateField = $driver === 'sqlite' ? 'strftime("%Y-%m", created_at)' : 'DATE_FORMAT(created_at, "%Y-%m")';

        // User growth data (last 12 months)
        $userGrowth = User::select(
            DB::raw("$dateField as month"),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Loan statistics by status
        $loansByStatus = Loan::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->status->getLabel() => $item->count]);

        // Transaction volume by type (last 30 days)
        $transactionsByType = Transaction::select('type', DB::raw('SUM(amount) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->where('status', TransactionStatus::Success)
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->type->getLabel() => $item->total]);

        // Monthly loan repayments (last 6 months)
        $loanRepayments = LoanPayment::select(
            DB::raw("$dateField as month"),
            DB::raw('SUM(amount) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Share purchases over time (last 12 months)
        $sharePurchases = Share::select(
            DB::raw("$dateField as month"),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(quantity) as total_quantity')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Revenue breakdown
        $shareSettings = app(ShareSettings::class);
        $revenueData = [
            'loan_interest' => LoanPayment::sum('amount') - Loan::sum('amount'),
            'share_purchases' => Share::where('status', ShareStatus::APPROVED)->sum('quantity') * $shareSettings->share_price,
            'transactions' => Transaction::where('status', TransactionStatus::Success)
                ->whereIn('transactable_type', [
                    AirtimePlan::class,
                    DataPlan::class,
                    CablePlan::class,
                    ElectricityPlan::class,
                    EducationPlan::class,
                ])
                ->sum('amount'),
        ];

        return $this->view()->with([
            'userGrowth' => $userGrowth,
            'loansByStatus' => $loansByStatus,
            'transactionsByType' => $transactionsByType,
            'loanRepayments' => $loanRepayments,
            'sharePurchases' => $sharePurchases,
            'revenueData' => $revenueData,
        ])->layout('layouts::admin');
    }
}; ?>

        <x-ui.chart title="User Growth" description="New user registrations over the last 12 months">
            <canvas id="userGrowthChart"></canvas>
        </x-ui.chart>

        <!-- Loans by Status -->
        <x-ui.chart title="Loans by Status" description="Distribution of loans across different statuses">
            <canvas id="loanStatusChart"></canvas>
        </x-ui.chart>

        <!-- Transaction Volume by Type -->
        <x-ui.chart title="Transaction Volume" description="Transaction amounts by type (Last 30 days)">
            <canvas id="transactionVolumeChart"></canvas>
        </x-ui.chart>

        <!-- Loan Repayments -->
        <x-ui.chart title="Loan Repayments" description="Monthly loan repayment trends (Last 6 months)">
            <canvas id="loanRepaymentsChart"></canvas>
        </x-ui.chart>

        <!-- Share Purchases -->
        <x-ui.chart title="Share Purchases" description="Share purchase activity over the last 12 months" class="lg:col-span-2">
            <canvas id="sharePurchasesChart"></canvas>
        </x-ui.chart>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js default configuration
    Chart.defaults.color = document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280';
    Chart.defaults.borderColor = document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb';

    // User Growth Chart
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels: @json($userGrowth->pluck('month')),
            datasets: [{
                label: 'New Users',
                data: @json($userGrowth->pluck('count')),
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

    // Loan Status Chart
    new Chart(document.getElementById('loanStatusChart'), {
        type: 'doughnut',
        data: {
            labels: @json($loansByStatus->keys()),
            datasets: [{
                data: @json($loansByStatus->values()),
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)',
                    'rgb(168, 85, 247)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Transaction Volume Chart
    new Chart(document.getElementById('transactionVolumeChart'), {
        type: 'bar',
        data: {
            labels: @json($transactionsByType->keys()),
            datasets: [{
                label: 'Amount (₦)',
                data: @json($transactionsByType->values()),
                backgroundColor: 'rgba(168, 85, 247, 0.8)',
                borderColor: 'rgb(168, 85, 247)',
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

    // Loan Repayments Chart
    new Chart(document.getElementById('loanRepaymentsChart'), {
        type: 'bar',
        data: {
            labels: @json($loanRepayments->pluck('month')),
            datasets: [{
                label: 'Repayments (₦)',
                data: @json($loanRepayments->pluck('total')),
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

    // Share Purchases Chart
    new Chart(document.getElementById('sharePurchasesChart'), {
        type: 'line',
        data: {
            labels: @json($sharePurchases->pluck('month')),
            datasets: [
                {
                    label: 'Number of Purchases',
                    data: @json($sharePurchases->pluck('count')),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    yAxisID: 'y',
                    tension: 0.4
                },
                {
                    label: 'Total Quantity',
                    data: @json($sharePurchases->pluck('total_quantity')),
                    borderColor: 'rgb(168, 85, 247)',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
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
