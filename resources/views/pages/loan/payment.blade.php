<?php

use App\Actions\Loans\MakeLoanPaymentAction;
use App\Livewire\Concerns\HasToast;
use App\Models\Loan;
use Illuminate\Support\Number;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public Loan $loan;

    #[Validate(['required', 'numeric', 'min:0.01'])]
    public float $paymentAmount = 0;

    public float $walletBalance = 0;

    public function mount(Loan $loan)
    {
        // Ensure user can only make payments on their own loans
        if ($loan->user_id !== auth()->id()) {
            abort(403);
        }

        $this->loan = $loan;
        $this->paymentAmount = $loan->monthly_payment;
        $this->walletBalance = auth()->user()->wallet_balance ?? 0;
    }

    public function makePayment(MakeLoanPaymentAction $paymentAction)
    {
        $this->validate([
            'paymentAmount' => [
                'required',
                'numeric',
                'min:0.01',
                "max:{$this->loan->balance_remaining}",
            ],
        ]);

        try {
            $payment = $paymentAction->execute($this->loan, $this->paymentAmount);

            $this->toastSuccess('Payment of '.Number::currency($this->paymentAmount).' made successfully!');

            // Refresh loan data
            $this->loan = $this->loan->fresh();
            $this->walletBalance = auth()->user()->wallet_balance ?? 0;

            // Reset to default payment amount
            if ($this->loan->status === \App\Enums\LoanStatus::ACTIVE) {
                $this->paymentAmount = $this->loan->monthly_payment;
            } else {
                $this->paymentAmount = 0;
            }

            $this->dispatch('payment-made');
        } catch (\Exception $e) {
            $this->toastError($e->getMessage());
        }
    }

    public function setFullPayment()
    {
        $this->paymentAmount = $this->loan->balance_remaining;
    }

    public function setMonthlyPayment()
    {
        $this->paymentAmount = $this->loan->monthly_payment;
    }

    public function render()
    {
        return $this->view()
            ->title('Make Loan Payment')
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-2xl mx-auto p-6">
    <x-page-header 
        heading="Make Loan Payment" 
        description="Process a payment for your loan"
    >
        <x-slot name="actions">
            <x-ui.button variant="outline" wire:navigate href="/loans/{{ $loan->id }}">
                ← Back to Loan Details
            </x-ui.button>
        </x-slot>
    </x-page-header>

    <x-ui.card>

        @if ($loan->status === \App\Enums\LoanStatus::ACTIVE)
            <!-- Loan Summary -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-neutral-800/50 p-4 rounded-lg overflow-hidden">
                    <p class="text-sm text-gray-600 dark:text-neutral-400">Balance Remaining</p>
                    <p class="text-xl sm:text-2xl font-bold text-warning-600 truncate" title="{{ Number::currency($loan->balance_remaining) }}">{{ Number::currency($loan->balance_remaining) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-neutral-800/50 p-4 rounded-lg overflow-hidden">
                    <p class="text-sm text-gray-600 dark:text-neutral-400">Wallet Balance</p>
                    <p class="text-xl sm:text-2xl font-bold {{ $walletBalance >= $paymentAmount ? 'text-success-600' : 'text-error-600' }} truncate" title="{{ Number::currency($walletBalance) }}">
                        {{ Number::currency($walletBalance) }}
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <div class="bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800 p-4 rounded-lg overflow-hidden">
                    <p class="text-sm text-gray-700 dark:text-primary-400 mb-2">Monthly Payment Amount</p>
                    <p class="text-2xl sm:text-3xl font-bold text-primary-600 truncate" title="{{ Number::currency($loan->monthly_payment) }}">{{ Number::currency($loan->monthly_payment) }}</p>
                </div>
            </div>

            <!-- Payment Form -->
            <form wire:submit="makePayment">
                <div class="space-y-4">
                    <x-ui.field>
                        <x-ui.label>{{ __('Payment Amount') }}</x-ui.label>
                        <x-ui.input
                            wire:model="paymentAmount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            :max="$loan->balance_remaining"
                            placeholder="Enter payment amount"
                            required
                        />
                        <x-ui.error name="paymentAmount" />
                    </x-ui.field>

                    <!-- Quick Actions -->
                    <div class="flex gap-2">
                        <x-ui.button 
                            type="button" 
                            variant="outline" 
                            size="sm" 
                            wire:click="setMonthlyPayment"
                        >
                            Monthly Payment
                        </x-ui.button>
                        <x-ui.button 
                            type="button" 
                            variant="outline" 
                            size="sm" 
                            wire:click="setFullPayment"
                        >
                            Pay Full Balance
                        </x-ui.button>
                    </div>

                    @if ($walletBalance < $paymentAmount)
                        <x-ui.alerts type="warning">
                            Insufficient wallet balance. You need {{ Number::currency($paymentAmount - $walletBalance) }} more.
                        </x-ui.alerts>
                    @endif

                    <div class="border-t pt-4">
                        <x-ui.button 
                            type="submit" 
                            class="w-full" 
                            :disabled="$walletBalance < $paymentAmount || $paymentAmount <= 0"
                        >
                            Process Payment
                        </x-ui.button>
                    </div>
                </div>
            </form>

            <!-- Next Payment Info -->
            @if ($loan->next_payment_date)
                <div class="mt-6 pt-6 border-t">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Next Payment Due</p>
                            <p class="font-semibold {{ $loan->is_overdue ? 'text-error-600' : 'text-gray-900' }}">
                                {{ $loan->next_payment_date->format('F d, Y') }}
                            </p>
                        </div>
                        @if ($loan->is_overdue)
                            <x-ui.badge color="danger">
                                Overdue
                            </x-ui.badge>
                        @else
                            <x-ui.badge color="success">
                                {{ $loan->next_payment_date->diffForHumans() }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <x-ui.alerts type="info">
                This loan is not active and cannot accept payments.
            </x-ui.alerts>
        @endif
    </x-ui.card>
</div>