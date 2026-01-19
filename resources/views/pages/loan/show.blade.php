<?php

use App\Actions\Loans\CalculateLoanScheduleAction;
use App\Models\Loan;
use Livewire\Component;

new class extends Component
{
    public Loan $loan;

    public array $paymentSchedule = [];

    public function mount(Loan $loan)
    {
        // Ensure user can only view their own loans
        if ($loan->user_id !== auth()->id()) {
            abort(403);
        }

        $this->loan = $loan;
        $this->loadPaymentSchedule();
    }

    public function loadPaymentSchedule()
    {
        $scheduleAction = new CalculateLoanScheduleAction;
        $this->paymentSchedule = $scheduleAction->execute($this->loan);
    }

    public function render()
    {
        $this->loan->load(['payments' => function ($query) {
            $query->latest();
        }]);

        return $this->view()
            ->title('Loan Details')
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-7xl mx-auto p-6">
    <x-page-header 
        heading="Loan Details" 
        description="View your loan information and payment schedule"
    >
        <x-slot name="actions">
            <x-ui.badge :color="$loan->status_badge" size="lg">
                {{ $loan->status->getLabel() }}
            </x-ui.badge>
            <x-ui.button variant="outline" wire:navigate href="/loans">
                ← Back to Loans
            </x-ui.button>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Loan Summary -->
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>

                <div class="grid grid-cols-2 gap-6">
                    <div class="overflow-hidden">
                        <p class="text-sm text-gray-600 dark:text-neutral-400">Loan Amount</p>
                        <p class="text-xl sm:text-2xl font-bold truncate" title="{{ Number::currency($loan->amount) }}">{{ Number::currency($loan->amount) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Loan Level</p>
                        <p class="text-2xl font-bold">{{ $loan->loanLevel->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Interest Rate</p>
                        <p class="text-xl font-semibold">{{ number_format($loan->interest_rate, 2) }}%</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Installment Period</p>
                        <p class="text-xl font-semibold">{{ $loan->installment_months }} months</p>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm text-gray-600 dark:text-neutral-400">Monthly Payment</p>
                        <p class="text-lg sm:text-xl font-semibold text-primary-600 truncate" title="{{ Number::currency($loan->monthly_payment) }}">{{ Number::currency($loan->monthly_payment) }}</p>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm text-gray-600 dark:text-neutral-400">Total Repayment</p>
                        <p class="text-lg sm:text-xl font-semibold truncate" title="{{ Number::currency($loan->total_repayment) }}">{{ Number::currency($loan->total_repayment) }}</p>
                    </div>
                </div>

                <!-- Progress -->
                <div class="mt-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium">Repayment Progress</span>
                        <span class="text-sm font-medium">{{ number_format($loan->progress_percentage, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-gradient-to-r from-primary-500 to-success-500 h-4 rounded-full transition-all" 
                             style="width: {{ $loan->progress_percentage }}%"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-sm">
                        <span class="text-success-600 font-semibold">Paid: {{ Number::currency($loan->amount_paid) }}</span>
                        <span class="text-warning-600 font-semibold">Remaining: {{ Number::currency($loan->balance_remaining) }}</span>
                    </div>
                </div>

                @if ($loan->status === \App\Enums\LoanStatus::ACTIVE)
                    <div class="mt-6">
                        <x-ui.button wire:navigate href="/loans/{{ $loan->id }}/payment" class="w-full">
                            Make Payment
                        </x-ui.button>
                    </div>
                @endif
            </x-ui.card>

            <!-- Payment Schedule -->
            <x-ui.card>
                <x-slot name="header">
                    <h3 class="text-xl font-bold">Payment Schedule</h3>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase">Due Date</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase">Payment</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase">Principal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase">Interest</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($paymentSchedule as $schedule)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $schedule['payment_number'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($schedule['due_date'])->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">{{ Number::currency($schedule['payment_amount']) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ Number::currency($schedule['principal_amount']) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ Number::currency($schedule['interest_amount']) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ Number::currency($schedule['balance_remaining']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Important Dates -->
            <x-ui.card>
                <x-slot name="header">
                    <h3 class="font-bold">Important Dates</h3>
                </x-slot>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Applied</p>
                        <p class="font-semibold">{{ $loan->applied_at?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                    @if ($loan->disbursed_at)
                        <div>
                            <p class="text-gray-600">Disbursed</p>
                            <p class="font-semibold">{{ $loan->disbursed_at->format('M d, Y') }}</p>
                        </div>
                    @endif
                    @if ($loan->next_payment_date && $loan->status === \App\Enums\LoanStatus::ACTIVE)
                        <div>
                            <p class="text-gray-600">Next Payment</p>
                            <p class="font-semibold {{ $loan->is_overdue ? 'text-error-600' : 'text-primary-600' }}">
                                {{ $loan->next_payment_date->format('M d, Y') }}
                            </p>
                            @if ($loan->is_overdue)
                                <x-ui.badge color="danger" size="sm" class="mt-1">Overdue</x-ui.badge>
                            @endif
                        </div>
                    @endif
                    @if ($loan->fully_paid_at)
                        <div>
                            <p class="text-gray-600">Fully Paid</p>
                            <p class="font-semibold text-success-600">{{ $loan->fully_paid_at->format('M d, Y') }}</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Payment History -->
            <x-ui.card>
                <x-slot name="header">
                    <h3 class="font-bold">Recent Payments</h3>
                </x-slot>

                @if ($loan->payments->count() > 0)
                    <div class="space-y-3">
                        @foreach ($loan->payments->take(5) as $payment)
                            <div class="border-l-4 border-success-500 pl-3 py-2">
                                <p class="font-semibold truncate" title="{{ Number::currency($payment->amount) }}">{{ Number::currency($payment->amount) }}</p>
                                <p class="text-xs text-gray-600 dark:text-neutral-400">{{ $payment->payment_date->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500 truncate" title="P: {{ Number::currency($payment->principal_amount) }} | I: {{ Number::currency($payment->interest_amount) }}">
                                    P: {{ Number::currency($payment->principal_amount) }} | 
                                    I: {{ Number::currency($payment->interest_amount) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-600 text-center py-4">No payments made yet</p>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>