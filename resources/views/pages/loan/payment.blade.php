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
        if ($loan->user_id != auth()->id()) {
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
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-neutral-50 dark:bg-neutral-900/50 p-6 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Remaining Balance</p>
                    <p class="text-xl font-bold text-amber-500 truncate" title="{{ Number::currency($loan->balance_remaining, 'NGN') }}">{{ Number::currency($loan->balance_remaining, 'NGN') }}</p>
                </div>
                <div class="bg-neutral-50 dark:bg-neutral-900/50 p-6 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 overflow-hidden">
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Wallet Balance</p>
                    <p class="text-xl font-bold {{ $walletBalance >= $paymentAmount ? 'text-success' : 'text-error' }} truncate" title="{{ Number::currency($walletBalance, 'NGN') }}">
                        {{ Number::currency($walletBalance, 'NGN') }}
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <div class="bg-primary/5 dark:bg-primary/10 border border-primary/20 dark:border-primary/80 p-6 rounded-[--radius-box] overflow-hidden">
                    <p class="text-[10px] font-bold text-primary dark:text-primary-400 uppercase tracking-widest mb-2">Monthly Installment</p>
                    <p class="text-3xl font-bold text-primary truncate" title="{{ Number::currency($loan->monthly_payment, 'NGN') }}">{{ Number::currency($loan->monthly_payment, 'NGN') }}</p>
                </div>
            </div>

            <!-- Payment Form -->
            <form wire:submit="makePayment">
                <div class="space-y-6">
                    <x-ui.field>
                        <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Payment Amount') }}</x-ui.label>
                        <x-ui.input
                            wire:model="paymentAmount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            :max="$loan->balance_remaining"
                            placeholder="0.00"
                            class="text-base font-bold tracking-widest h-14"
                            required
                        />
                        <x-ui.error name="paymentAmount" />
                    </x-ui.field>

                    <!-- Quick Actions -->
                    <div class="flex gap-4">
                        <x-ui.button 
                            type="button" 
                            variant="outline" 
                            size="sm" 
                            wire:click="setMonthlyPayment"
                            class="flex-1 rounded-[--radius-field] font-bold uppercase tracking-widest text-[10px]"
                        >
                            Monthly Installment
                        </x-ui.button>
                        <x-ui.button 
                            type="button" 
                            variant="outline" 
                            size="sm" 
                            wire:click="setFullPayment"
                            class="flex-1 rounded-[--radius-field] font-bold uppercase tracking-widest text-[10px]"
                        >
                            Pay Full Balance
                        </x-ui.button>
                    </div>

                    @if ($walletBalance < $paymentAmount)
                        <x-ui.alerts type="warning" class="bg-amber-50 text-amber-600 border-amber-100 rounded-[--radius-box]">
                            <p class="text-xs font-bold uppercase tracking-widest">Insufficient wallet balance</p>
                            <p class="text-[10px] mt-1">You need {{ Number::currency($paymentAmount - $walletBalance, 'NGN') }} more.</p>
                        </x-ui.alerts>
                    @endif

                    <div class="border-t border-neutral-100 dark:border-neutral-700 pt-6">
                        <x-ui.button 
                            type="submit" 
                            class="w-full h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20" 
                            :disabled="$walletBalance < $paymentAmount || $paymentAmount <= 0"
                        >
                            Process Payment
                        </x-ui.button>
                    </div>
                </div>
            </form>

            <!-- Next Payment Info -->
            @if ($loan->next_payment_date)
                <div class="mt-8 pt-8 border-t border-neutral-100 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Next Payment Due</p>
                            <p class="text-base font-bold text-neutral-900 dark:text-white">
                                {{ $loan->next_payment_date->format('F d, Y') }}
                            </p>
                        </div>
                        @if ($loan->is_overdue)
                            <x-ui.badge color="danger" class="text-[10px] font-bold uppercase tracking-widest">
                                Overdue
                            </x-ui.badge>
                        @else
                            <x-ui.badge color="success" class="text-[10px] font-bold uppercase tracking-widest">
                                Due {{ $loan->next_payment_date->diffForHumans() }}
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