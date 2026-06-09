<?php

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\LoanScheduleEntry;
use App\Enums\Loans\LoanStatus;
use App\Enums\Loans\LoanScheduleEntryStatus;
use App\Enums\Wallets\WalletType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Loan Details')] class extends Component {
    public Loan $loan;
    public ?array $payoffQuote = null;

    public function mount(Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            abort(403);
        }
        $this->loan = $loan;
    }

    /**
     * Get the next repayment due.
     */
    #[Computed]
    public function nextRepayment(): ?LoanScheduleEntry
    {
        return $this->loan->scheduleEntries()
            ->where('status', LoanScheduleEntryStatus::Pending)
            ->orderBy('due_date')
            ->first();
    }

    /**
     * Get the repayment amount.
     */
    #[Computed]
    public function repaymentAmount(): float
    {
        return $this->nextRepayment?->instalment_amount ?? 0;
    }

    /**
     * Get the total amount to pay (next instalment).
     */
    #[Computed]
    public function nextRepaymentAmount(): float
    {
        return $this->repaymentAmount;
    }

    /**
     * Pay the next instalment.
     */
    public function payInstalment(App\Actions\Loans\RepayLoanAction $action): void
    {
        $user = Auth::user();
        $amount = $this->nextRepaymentAmount;

        if ($user->wallet->available_balance < $amount) {
            Flux::toast(
                text: __('Insufficient funds in your wallet.'),
                variant: 'danger',
            );
            return;
        }

        try {
            $action->handle($this->loan, $user, $amount);

            Flux::toast(
                text: __('Successfully paid instalment of :amount', [
                    'amount' => Number::currency($amount),
                ]),
                variant: 'success',
            );

            $this->loan->refresh();
        } catch (\Exception $e) {
            Flux::toast(
                text: __('An error occurred during payment: ' . $e->getMessage()),
                variant: 'danger',
            );
        }
    }

    /**
     * Load the early payoff quote.
     */
    public function loadPayoffQuote(App\Actions\Loans\CalculateLoanPayoffAction $action): void
    {
        $this->payoffQuote = $action->handle($this->loan);
    }

    /**
     * Confirm and execute early payoff.
     */
    public function confirmPayoff(App\Actions\Loans\PayoffLoanAction $action): void
    {
        $user = Auth::user();
        if (! $this->payoffQuote) {
            return;
        }

        try {
            $action->handle($this->loan, $user);

            Flux::toast(
                text: __('Successfully settled loan early.'),
                variant: 'success',
            );

            $this->loan->refresh();
            $this->payoffQuote = null;

            $this->dispatch('close-modal', name: 'payoff-modal');
        } catch (\Exception $e) {
            Flux::toast(
                text: __('An error occurred: ' . $e->getMessage()),
                variant: 'danger',
            );
        }
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-4xl mx-auto space-y-6 animate-pulse">
            <div class="flex items-center space-x-4">
                <div class="h-10 w-10 bg-zinc-200 dark:bg-zinc-700 rounded-lg"></div>
                <div class="h-8 w-48 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 h-96 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
                <div class="lg:col-span-1 h-96 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
            </div>
        </div>
        HTML;
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <flux:button :href="route('loan.index')" variant="ghost" icon="arrow-left" inset="left" />
            <flux:heading size="xl">{{ __('Loan Details') }} #{{ $loan->id }}</flux:heading>
        </div>

        <flux:badge :color="$loan->status->getFluxColor()" :icon="$loan->status->getFluxIcon()" size="md">
            {{ $loan->status->getLabel() }}
        </flux:badge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <flux:card class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-wider font-semibold">{{ __('Principal Amount') }}</flux:text>
                        <flux:text weight="semibold">{{ Number::currency($loan->principal_amount) }}</flux:text>
                    </div>
                    <div class="space-y-1">
                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-wider font-semibold">{{ __('Interest Rate') }}</flux:text>
                        <flux:text weight="semibold">{{ $loan->interest_rate }}% ({{ $loan->interest_method->getLabel() }})</flux:text>
                    </div>
                    <div class="space-y-1">
                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-wider font-semibold">{{ __('Term') }}</flux:text>
                        <flux:text weight="semibold">{{ $loan->repayment_term_months }} {{ __('Months') }}</flux:text>
                    </div>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-wider font-semibold">{{ __('Date Applied') }}</flux:text>
                        <flux:text weight="semibold">{{ $loan->created_at->format('M j, Y') }}</flux:text>
                    </div>
                    <div class="space-y-1">
                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-wider font-semibold">{{ __('Outstanding Balance') }}</flux:text>
                        <flux:text weight="semibold" :class="$loan->outstanding_balance > 0 ? 'text-orange-600' : 'text-green-600'">
                            {{ Number::currency($loan->outstanding_balance ?? 0) }}
                        </flux:text>
                    </div>
                </div>

                @if ($loan->notes)
                    <flux:separator />
                    <div class="space-y-1">
                        <flux:text size="xs" class="text-zinc-500 uppercase tracking-wider font-semibold">{{ __('Notes') }}</flux:text>
                        <flux:text size="sm">{{ $loan->notes }}</flux:text>
                    </div>
                @endif
            </flux:card>

            <flux:card class="p-2 overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('#') }}</flux:table.column>
                        <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('Amount') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('Balance') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($loan->scheduleEntries as $entry)
                            <flux:table.row :key="$entry->id">
                                <flux:table.cell class="text-zinc-500">{{ $entry->instalment_number }}</flux:table.cell>
                                <flux:table.cell>{{ $entry->due_date->format('M j, Y') }}</flux:table.cell>
                                <flux:table.cell align="end">{{ Number::currency($entry->instalment_amount) }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$entry->status->getFluxColor()" size="sm">
                                        {{ $entry->status->getLabel() }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell align="end" class="text-zinc-500">
                                    {{ Number::currency($entry->remaining_amount) }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        <div class="lg:col-span-1 space-y-6">
            @if ($loan->status === LoanStatus::Disbursed && $this->nextRepayment)
                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ __('Repayment') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('Your next instalment is due on :date.', ['date' => $this->nextRepayment->due_date->format('M j, Y')]) }}
                    </flux:text>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <flux:text size="sm">{{ __('Instalment Amount') }}</flux:text>
                            <flux:text size="sm" weight="semibold">{{ Number::currency($this->nextRepaymentAmount) }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm">{{ __('Total Outstanding') }}</flux:text>
                            <flux:text size="sm" weight="semibold">{{ Number::currency($loan->outstanding_balance) }}</flux:text>
                        </div>
                    </div>

                    <flux:button variant="primary" class="w-full" wire:click="payInstalment">
                        {{ __('Pay Now') }}
                    </flux:button>
                </flux:card>
            @endif

            @if ($loan->status === LoanStatus::Disbursed)
                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ __('Early Payoff') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('Settle this loan in full today. A prepayment penalty may apply on future remaining interest.') }}
                    </flux:text>

                    <flux:modal.trigger name="payoff-modal">
                        <flux:button variant="primary" class="w-full" wire:click="loadPayoffQuote">
                            {{ __('Settle Loan Early') }}
                        </flux:button>
                    </flux:modal.trigger>
                </flux:card>
            @endif

            <flux:card class="space-y-4 bg-zinc-50 dark:bg-zinc-900 border-dashed">
                <flux:heading size="sm">{{ __('Help & Support') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('If you have any questions about your loan or repayment schedule, please contact our support team.') }}
                </flux:text>
                <flux:button variant="ghost" size="sm" class="w-full">{{ __('Contact Support') }}</flux:button>
            </flux:card>
        </div>
    </div>

    <flux:modal name="payoff-modal" class="md:w-[28rem] space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Settle Loan Early') }}</flux:heading>
            <flux:subheading>{{ __('Review the early payoff quote details.') }}</flux:subheading>
        </div>

        @if ($payoffQuote)
            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <flux:text>{{ __('Remaining Principal') }}</flux:text>
                    <flux:text weight="semibold">{{ Number::currency($payoffQuote['remaining_principal']) }}</flux:text>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <flux:text>{{ __('Accrued Interest') }}</flux:text>
                    <flux:text weight="semibold">{{ Number::currency($payoffQuote['accrued_interest']) }}</flux:text>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <flux:text>{{ __('Prepayment Penalty') }}</flux:text>
                    <flux:text weight="semibold">{{ Number::currency($payoffQuote['prepayment_penalty']) }}</flux:text>
                </div>
                <div class="flex justify-between pt-2">
                    <flux:text weight="semibold">{{ __('Total Payoff Amount') }}</flux:text>
                    <flux:text weight="bold" size="lg" class="text-violet-600">{{ Number::currency($payoffQuote['total_payoff_amount']) }}</flux:text>
                </div>
            </div>

            <div class="flex space-x-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="confirmPayoff">{{ __('Confirm Payoff') }}</flux:button>
            </div>
        @else
            <div class="flex justify-center py-6">
                <flux:spinner />
            </div>
        @endif
    </flux:modal>
</div>
