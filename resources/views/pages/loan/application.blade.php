<?php

use App\Actions\Loans\ApplyForLoanAction;
use App\Livewire\Concerns\HasToast;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public $requestedAmount = 0;

    public $eligibleAmount = 0;

    public $sharesValue = 0;

    public ?string $loanLevelName = null;

    public $maxLoanAmount = 0;

    public int $installmentMonths = 0;

    public $interestRate = 0;

    public $estimatedMonthlyPayment = 0;

    public function mount()
    {
        $this->calculateEligibility();
    }

    public function calculateEligibility()
    {
        $user = auth()->user();

        if (! $user->loan_level_id) {
            $this->eligibleAmount = 0;

            return;
        }

        $this->sharesValue = $user->getSharesValue();
        $this->eligibleAmount = $user->getLoanEligibilityAmount();

        $loanLevel = $user->loanLevel;
        $this->loanLevelName = $loanLevel->name;
        $this->maxLoanAmount = $loanLevel->maximum_loan_amount;
        $this->installmentMonths = $loanLevel->installment_period_months;
        $this->interestRate = $loanLevel->interest_rate;

        $this->calculateEstimate();
    }

    public function updatedRequestedAmount()
    {
        if ($this->requestedAmount > 0) {
            $this->calculateEstimate();
        }
    }

    public function calculateEstimate()
    {
        if ($this->requestedAmount > 0 && $this->installmentMonths > 0) {
            $monthlyRate = $this->interestRate / 100;

            if ($this->interestRate == 0) {
                $this->estimatedMonthlyPayment = $this->requestedAmount / $this->installmentMonths;
            } else {
                $factor = pow(1 + $monthlyRate, $this->installmentMonths);
                $this->estimatedMonthlyPayment = ($this->requestedAmount * $monthlyRate * $factor) / ($factor - 1);
            }
        } else {
            $this->estimatedMonthlyPayment = 0;
        }
    }

    public function apply(ApplyForLoanAction $applyAction)
    {
        $this->validate([
            'requestedAmount' => [
                'required',
                'numeric',
                'min:1',
                "max:{$this->eligibleAmount}",
            ],
        ]);

        try {
            $user = auth()->user();

            // Apply for loan
            $loan = $applyAction->execute($user, $this->requestedAmount);

            $this->toastSuccess('Loan application submitted successfully!');

            return redirect()->route('loans.details', $loan->id);
        } catch (\Exception $e) {
            $this->toastError($e->getMessage());
        }
    }

    public function render()
    {
        return $this->view()
            ->title('Loan Application')
            ->layout('layouts::app');
    }
};
?>

<div>
    <div class="max-w-4xl mx-auto p-6">
    <x-page-header 
        heading="Apply for Loan" 
        description="Submit your loan application"
    />

    <x-ui.card>

        <div class="space-y-6">
            <!-- Eligibility Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.card class="bg-primary/5 border-primary/20 shadow-none rounded-[--radius-box]">
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Your Shares Value</p>
                        <p class="text-xl font-bold text-primary">{{ Number::currency($sharesValue, 'NGN') }}</p>
                    </div>
                </x-ui.card>

                <x-ui.card class="bg-success/5 border-success/20 shadow-none rounded-[--radius-box]">
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Eligible Amount</p>
                        <p class="text-xl font-bold text-success">{{ Number::currency($eligibleAmount, 'NGN') }}</p>
                    </div>
                </x-ui.card>

                <x-ui.card class="bg-neutral-50 dark:bg-neutral-900/50 border-neutral-100 dark:border-neutral-700 shadow-none rounded-[--radius-box]">
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Loan Level</p>
                        <p class="text-xl font-bold text-purple-600">{{ $loanLevelName ?? 'N/A' }}</p>
                    </div>
                </x-ui.card>
            </div>

            @if ($eligibleAmount > 0)
                <!-- Loan Details -->
                <div class="bg-neutral-50 dark:bg-neutral-900/50 p-6 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700">
                    <h3 class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-4">Loan Terms</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center justify-between md:justify-start md:gap-4">
                            <span class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Interest Rate:</span>
                            <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ number_format($interestRate, 2) }}% per month</span>
                        </div>
                        <div class="flex items-center justify-between md:justify-start md:gap-4">
                            <span class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Repayment Period:</span>
                            <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ $installmentMonths }} months</span>
                        </div>
                    </div>
                </div>

                <!-- Application Form -->
                <form wire:submit="apply">
                    <div class="space-y-6">
                        <x-ui.field>
                            <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Requested Amount') }}</x-ui.label>
                            <x-ui.input
                                wire:model.live.debounce.500ms="requestedAmount"
                                type="number"
                                step="0.01"
                                min="1"
                                :max="$eligibleAmount"
                                placeholder="0.00"
                                class="text-base font-bold tracking-widest h-14"
                                required
                            />
                            <x-ui.error name="requestedAmount" />
                        </x-ui.field>

                        @if ($estimatedMonthlyPayment > 0)
                            <x-ui.alerts type="info" class="bg-primary/5 text-primary border-primary/20 rounded-[--radius-box]">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-[10px] font-bold uppercase tracking-widest">Monthly Payment:</span>
                                    <span class="font-bold text-xl">{{ Number::currency($estimatedMonthlyPayment, 'NGN') }}</span>
                                </div>
                            </x-ui.alerts>
                        @endif

                        <div class="flex gap-4 pt-4">
                            <x-ui.button type="submit" class="flex-1 h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20">
                                Apply for Loan
                            </x-ui.button>
                            <x-ui.button type="button" variant="outline" wire:click="calculateEligibility" class="h-14 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">
                                Recalculate
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            @else
                <x-ui.alerts type="warning">
                    You are not currently eligible for a loan. Please ensure you have:
                    <ul class="list-disc list-inside mt-2">
                        <li>A loan level assigned to your account</li>
                        <li>Sufficient shares (minimum 15% of loan amount)</li>
                        <li>No active loans</li>
                    </ul>
                </x-ui.alerts>
            @endif
        </div>
    </x-ui.card>
</div>
</div>