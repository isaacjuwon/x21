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
        if ($loan->user_id != auth()->id()) {
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
            <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="overflow-hidden">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Loan Amount</p>
                        <p class="text-xl font-bold text-neutral-900 dark:text-white truncate" title="{{ Number::currency($loan->amount, 'NGN') }}">{{ Number::currency($loan->amount, 'NGN') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Loan Level</p>
                        <p class="text-base font-bold text-purple-600 uppercase tracking-widest">{{ $loan->loanLevel?->name ?? 'Default' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Interest Rate</p>
                        <p class="text-base font-bold text-neutral-900 dark:text-white">{{ number_format($loan->interest_rate, 2) }}%</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Installment Period</p>
                        <p class="text-base font-bold text-neutral-900 dark:text-white">{{ $loan->installment_months }} months</p>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Monthly Payment</p>
                        <p class="text-base font-bold text-primary truncate" title="{{ Number::currency($loan->monthly_payment, 'NGN') }}">{{ Number::currency($loan->monthly_payment, 'NGN') }}</p>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Total Repayment</p>
                        <p class="text-base font-bold text-neutral-900 dark:text-white truncate" title="{{ Number::currency($loan->total_repayment, 'NGN') }}">{{ Number::currency($loan->total_repayment, 'NGN') }}</p>
                    </div>
                </div>

                <!-- Progress -->
                <div class="mt-8 pt-8 border-t border-neutral-100 dark:border-neutral-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Repayment Progress</span>
                        <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ number_format($loan->progress_percentage, 1) }}%</span>
                    </div>
                    <div class="w-full bg-neutral-100 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary to-success h-full rounded-full transition-all duration-500" 
                             style="width: {{ $loan->progress_percentage }}%"></div>
                    </div>
                    <div class="flex justify-between mt-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-1">Paid</span>
                            <span class="text-xs font-bold text-success">{{ Number::currency($loan->amount_paid, 'NGN') }}</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-1">Balance Remaining</span>
                            <span class="text-xs font-bold text-amber-500">{{ Number::currency($loan->balance_remaining, 'NGN') }}</span>
                        </div>
                    </div>
                </div>

                @if ($loan->status === \App\Enums\LoanStatus::ACTIVE)
                    <div class="mt-8 pt-4">
                        <x-ui.button wire:navigate href="/loans/{{ $loan->id }}/payment" class="w-full h-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20">
                            Make Payment
                        </x-ui.button>
                    </div>
                @endif
            </x-ui.card>

            <!-- Payment Schedule -->
            <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none">
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Payment Schedule</h3>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y border border-neutral-100 dark:border-neutral-700 divide-neutral-100 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-900/50 text-neutral-500 dark:text-neutral-400 uppercase tracking-widest text-[10px] font-bold">
                            <tr>
                                <th class="px-6 py-4 text-left">#</th>
                                <th class="px-6 py-4 text-left">Due Date</th>
                                <th class="px-6 py-4 text-right">Payment</th>
                                <th class="px-6 py-4 text-right">Principal</th>
                                <th class="px-6 py-4 text-right">Interest</th>
                                <th class="px-6 py-4 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                            @foreach ($paymentSchedule as $schedule)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50 transition-colors">
                                    <td class="px-6 py-4 text-xs font-bold text-neutral-900 dark:text-white">{{ $schedule['payment_number'] }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-neutral-500 dark:text-neutral-400">{{ \Carbon\Carbon::parse($schedule['due_date'])->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-right text-neutral-900 dark:text-white">{{ Number::currency($schedule['payment_amount'], 'NGN') }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-right text-neutral-500 dark:text-neutral-400">{{ Number::currency($schedule['principal_amount'], 'NGN') }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-right text-neutral-500 dark:text-neutral-400">{{ Number::currency($schedule['interest_amount'], 'NGN') }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-right text-neutral-500 dark:text-neutral-400">{{ Number::currency($schedule['balance_remaining'], 'NGN') }}</td>
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
            <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none">
                <x-slot name="header">
                    <h3 class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-widest">Important Dates</h3>
                </x-slot>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Applied</span>
                        <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ $loan->applied_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                    @if ($loan->disbursed_at)
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Disbursed</span>
                            <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ $loan->disbursed_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if ($loan->next_payment_date && $loan->status === \App\Enums\LoanStatus::ACTIVE)
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Next Payment</span>
                            <span class="text-xs font-bold {{ $loan->is_overdue ? 'text-error' : 'text-primary' }}">
                                {{ $loan->next_payment_date->format('M d, Y') }}
                            </span>
                        </div>
                    @endif
                    @if ($loan->fully_paid_at)
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Fully Paid</span>
                            <span class="text-xs font-bold text-success">{{ $loan->fully_paid_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Payment History -->
            <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none">
                <x-slot name="header">
                    <h3 class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-widest">Recent Payments</h3>
                </x-slot>

                @if ($loan->payments->count() > 0)
                    <div class="space-y-4">
                        @foreach ($loan->payments->take(5) as $payment)
                            <div class="border-l-4 border-success pl-4 py-2 bg-neutral-50 dark:bg-neutral-900/50 rounded-r-[--radius-field] border-y border-r border-neutral-100 dark:border-neutral-700">
                                <p class="text-sm font-bold text-neutral-900 dark:text-white truncate mb-1" title="{{ Number::currency($payment->amount, 'NGN') }}">{{ Number::currency($payment->amount, 'NGN') }}</p>
                                <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-2">{{ $payment->payment_date->format('M d, Y') }}</p>
                                <div class="flex items-center gap-2 text-[10px] font-bold text-neutral-400 uppercase tracking-widest">
                                    <span>P: <span class="text-neutral-600 dark:text-white">{{ Number::currency($payment->principal_amount, 'NGN') }}</span></span>
                                    <span class="text-neutral-200 dark:text-neutral-700">|</span>
                                    <span>I: <span class="text-neutral-600 dark:text-white">{{ Number::currency($payment->interest_amount, 'NGN') }}</span></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center bg-neutral-50/50 dark:bg-neutral-900/20 rounded-[--radius-box] border border-dashed border-neutral-100 dark:border-neutral-700">
                        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">No payments made yet</p>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>