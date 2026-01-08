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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-ui.card>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Your Shares Value</p>
                        <p class="text-2xl font-bold text-primary">{{ Number::currency($sharesValue) }}</p>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Eligible Loan Amount</p>
                        <p class="text-2xl font-bold text-success-600">{{ Number::currency($eligibleAmount) }}</p>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Loan Level</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $loanLevelName ?? 'N/A' }}</p>
                    </div>
                </x-ui.card>
            </div>

            @if ($eligibleAmount > 0)
                <!-- Loan Details -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold mb-3">Loan Terms</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-600">Interest Rate:</span>
                            <span class="font-semibold">{{ number_format($interestRate, 2) }}% per month</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Repayment Period:</span>
                            <span class="font-semibold">{{ $installmentMonths }} months</span>
                        </div>
                    </div>
                </div>

                <!-- Application Form -->
                <form wire:submit="apply">
                    <div class="space-y-4">
                        <x-ui.field>
                            <x-ui.label>{{ __('Requested Amount') }}</x-ui.label>
                            <x-ui.input
                                wire:model.live.debounce.500ms="requestedAmount"
                                type="number"
                                step="0.01"
                                min="1"
                                :max="$eligibleAmount"
                                placeholder="Enter loan amount"
                                required
                            />
                            <x-ui.error name="requestedAmount" />
                        </x-ui.field>

                        @if ($estimatedMonthlyPayment > 0)
                            <x-ui.alerts type="info">
                                <div class="flex justify-between items-center">
                                    <span>Estimated Monthly Payment:</span>
                                    <span class="font-bold text-lg">{{ Number::currency($estimatedMonthlyPayment) }}</span>
                                </div>
                            </x-ui.alerts>
                        @endif

                        <div class="flex gap-3">
                            <x-ui.button type="submit" class="flex-1">
                                Apply for Loan
                            </x-ui.button>
                            <x-ui.button type="button" variant="outline" wire:click="calculateEligibility">
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