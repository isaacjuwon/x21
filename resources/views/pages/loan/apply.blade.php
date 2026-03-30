<?php

use App\Actions\Loans\CheckLoanEligibilityAction;
use App\Enums\Loans\InterestMethod;
use App\Enums\Loans\LoanStatus;
use App\Models\Loan;
use App\Models\LoanLevel;
use App\Settings\LoanSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Apply for a Loan'), Defer] class extends Component {
    public ?float $amount = null;

    public ?int $term_months = null;

    public ?string $interest_method = null;

    public string $notes = '';

    public function mount(LoanSettings $settings): void
    {
        $this->interest_method = $settings->interest_method->value;
    }

    /**
     * Get the user's current loan level.
     */
    #[Computed]
    public function loanLevel(): ?LoanLevel
    {
        return Auth::user()->loanLevel;
    }

    /**
     * Get the interest rate.
     */
    #[Computed]
    public function interestRate(): float
    {
        return $this->loanLevel?->interest_rate ?? app(LoanSettings::class)->default_interest_rate;
    }

    /**
     * Get the minimum allowed amount.
     */
    #[Computed]
    public function minAmount(): float
    {
        return $this->loanLevel?->min_amount ?? app(LoanSettings::class)->min_amount;
    }

    /**
     * Get the maximum allowed amount.
     */
    #[Computed]
    public function maxAmount(): float
    {
        return $this->loanLevel?->max_amount ?? app(LoanSettings::class)->max_amount;
    }

    /**
     * Get the max term months.
     */
    #[Computed]
    public function maxTermMonths(): int
    {
        return $this->loanLevel?->max_term_months ?? 60;
    }

    /**
     * Get the loan level name.
     */
    #[Computed]
    public function loanLevelName(): ?string
    {
        return $this->loanLevel?->name;
    }

    /**
     * Get the auto-approval status.
     */
    #[Computed]
    public function autoApprove(): bool
    {
        return app(LoanSettings::class)->auto_approve;
    }

    /**
     * Get the share requirement percentage.
     */
    #[Computed]
    public function minSharesPercentage(): float
    {
        return app(LoanSettings::class)->min_shares_percentage;
    }

    /**
     * Get the current interest method label.
     */
    #[Computed]
    public function interestMethodLabel(): string
    {
        return InterestMethod::from($this->interest_method)->getLabel();
    }

    /**
     * Check current eligibility for the requested amount.
     */
    #[Computed]
    public function eligibilityStatus(): array
    {
        $checkAction = app(CheckLoanEligibilityAction::class);
        $amountToCheck = (float) ($this->amount ?? $this->minAmount);
        $result = $checkAction->handle(Auth::user(), $amountToCheck);

        return [
            'eligible' => $result->passed,
            'reason' => $result->passed ? __('You meet all eligibility criteria for this amount.') : $result->failingSpecification->failureReason(),
        ];
    }

    /**
     * Generate a preview of the loan schedule.
     */
    #[Computed]
    public function previewSchedule(): array
    {
        if (! $this->amount || ! $this->term_months || $this->amount <= 0 || $this->term_months <= 0) {
            return [];
        }

        $principal = (float) $this->amount;
        $rate = (float) $this->interestRate / 100;
        $term = (int) $this->term_months;

        if ($this->interest_method === InterestMethod::FlatRate->value) {
            $totalInterest = $principal * $rate * ($term / 12);
            $instalmentAmount = round(($principal + $totalInterest) / $term, 2);
            $interestComponent = round($totalInterest / $term, 2);
            $principalComponent = round($principal / $term, 2);

            $entries = [];
            for ($n = 1; $n <= $term; $n++) {
                $outstandingBalance = round($principal - ($principalComponent * $n), 2);
                $entries[] = [
                    'instalment_number' => $n,
                    'due_date' => now()->addMonths($n)->format('Y-m-d'),
                    'instalment_amount' => $instalmentAmount,
                    'principal_component' => $principalComponent,
                    'interest_component' => $interestComponent,
                    'outstanding_balance' => max(0, $outstandingBalance),
                ];
            }

            return $entries;
        } else {
            // Reducing Balance
            $monthlyRate = $rate / 12;
            if ($monthlyRate == 0) {
                $instalmentAmount = round($principal / $term, 2);
            } else {
                $instalmentAmount = round(
                    $principal * $monthlyRate / (1 - (1 + $monthlyRate) ** (-$term)),
                    2
                );
            }

            $entries = [];
            $currentBalance = $principal;
            for ($n = 1; $n <= $term; $n++) {
                $interestComponent = round($currentBalance * $monthlyRate, 2);
                $principalComponent = round($instalmentAmount - $interestComponent, 2);
                $currentBalance = round($currentBalance - $principalComponent, 2);

                $entries[] = [
                    'instalment_number' => $n,
                    'due_date' => now()->addMonths($n)->format('Y-m-d'),
                    'instalment_amount' => $instalmentAmount,
                    'principal_component' => $principalComponent,
                    'interest_component' => $interestComponent,
                    'outstanding_balance' => max(0, $currentBalance),
                ];
            }

            return $entries;
        }
    }

    /**
     * Submit the loan application.
     */
    public function apply(CheckLoanEligibilityAction $checkEligibility): void
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:'.$this->minAmount],
            'term_months' => ['required', 'integer', 'min:1', 'max:'.$this->maxTermMonths],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            // Check eligibility first
            $result = $checkEligibility->handle(Auth::user(), $this->amount);

            if (! $result->passed) {
                $this->addError('amount', $result->failingSpecification->failureReason());

                return;
            }

            $loan = Loan::create([
                'user_id' => Auth::id(),
                'principal_amount' => $this->amount,
                'outstanding_balance' => $this->amount, // Disbursal will add interest
                'interest_rate' => $this->interestRate,
                'repayment_term_months' => $this->term_months,
                'interest_method' => $this->interest_method,
                'status' => $this->autoApprove ? LoanStatus::Disbursed : LoanStatus::Active,
                'notes' => $this->notes,
            ]);

            Flux::toast(
                text: __('Loan application submitted successfully.'),
                variant: 'success',
            );

            $this->redirect(route('loan.index'), navigate: true);
        } catch (\App\Exceptions\Loans\LoanIneligibleException $e) {
            $this->addError('amount', $e->getMessage());
        } catch (\Exception $e) {
            Flux::toast(
                text: __('An error occurred during application: '.$e->getMessage()),
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
                <div class="lg:col-span-1 h-96 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
                <div class="lg:col-span-2 h-96 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
            </div>
        </div>
        HTML;
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
        <flux:button :href="route('loan.index')" variant="ghost" icon="heroicon-o-arrow-left" inset="left" />
        <flux:heading size="xl">{{ __('Apply for a Loan') }}</flux:heading>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <flux:card class="space-y-6">
                @php
                    $eligibility = $this->eligibilityStatus;
                @endphp

                <flux:callout 
                    :icon="$eligibility['eligible'] ? 'check-circle' : 'exclamation-triangle'" 
                    :color="$eligibility['eligible'] ? 'green' : 'red'" 
                    :variant="$eligibility['eligible'] ? 'success' : 'danger'"
                >
                    <flux:callout.heading class="font-bold text-lg">
                        {{ $eligibility['eligible'] ? __('Eligible') : __('Ineligible') }}
                    </flux:callout.heading>
                    <flux:callout.text>
                        <p class="mb-3 font-medium">{{ $eligibility['reason'] }}</p>
                        
                        <div class="pt-3 border-t border-current/10">
                            <flux:text size="sm" class="uppercase tracking-wider font-bold mb-2 block opacity-80">{{ __('Your Current Limits') }}:</flux:text>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                <li>{{ __('Level') }}: <span class="font-bold">{{ $this->loanLevelName ?? __('Basic') }}</span></li>
                                <li>{{ __('Duration') }}: <span class="font-bold">{{ __('Up to :months Months', ['months' => $this->maxTermMonths]) }}</span></li>
                                <li>{{ __('Interest') }}: <span class="font-bold">{{ $this->interestRate }}% ({{ $this->interestMethodLabel }})</span></li>
                                <li>{{ __('Share Req.') }}: <span class="font-bold">{{ $this->minSharesPercentage }}%</span> {{ __('of amount') }}</li>
                            </ul>
                        </div>
                    </flux:callout.text>
                </flux:callout>

                @if(!$this->loanLevelName)
                    <flux:callout variant="warning" icon="exclamation-circle" heading="Standard Limits Apply">
                        <flux:text size="sm">You are currently on global loan limits. Complete more transactions to unlock higher loan levels and better interest rates.</flux:text>
                    </flux:callout>
                @endif
                <div class="space-y-4">
                    <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wider font-semibold">
                        {{ $this->loanLevelName ? __('Your Loan Level') : __('Global Loan Limits') }}
                    </flux:heading>
                    <div class="space-y-2">
                        @if ($this->loanLevelName)
                            <div class="flex justify-between">
                                <flux:text size="sm">{{ __('Level') }}</flux:text>
                                <flux:text size="sm" weight="semibold">{{ $this->loanLevelName }}</flux:text>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <flux:text size="sm">{{ __('Interest Rate') }}</flux:text>
                            <flux:text size="sm" weight="semibold">{{ $this->interestRate }}%</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm">{{ __('Min Amount') }}</flux:text>
                            <flux:text size="sm" weight="semibold">{{ Number::currency($this->minAmount) }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text size="sm">{{ __('Max Amount') }}</flux:text>
                            <flux:text size="sm" weight="semibold">{{ Number::currency($this->maxAmount) }}</flux:text>
                        </div>
                        @if ($this->loanLevelName)
                            <div class="flex justify-between">
                                <flux:text size="sm">{{ __('Max Term') }}</flux:text>
                                <flux:text size="sm" weight="semibold">{{ $this->maxTermMonths }} {{ __('Months') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </div>

                <flux:separator />

                <form wire:submit="apply" class="space-y-6">
                    <flux:input
                        wire:model.live="amount"
                        type="number"
                        step="0.01"
                        :label="__('Requested Amount')"
                        placeholder="0.00"
                        icon="heroicon-o-currency-naira"
                        required
                        :min="$this->minAmount"
                        :max="$this->maxAmount"
                    />

                    <flux:input
                        wire:model.live="term_months"
                        type="number"
                        :label="__('Repayment Term (Months)')"
                        placeholder="12"
                        icon="heroicon-o-calendar"
                        required
                        min="1"
                        :max="$this->maxTermMonths"
                    />

                    <flux:textarea
                        wire:model="notes"
                        :label="__('Purpose of Loan (Optional)')"
                        placeholder="{{ __('Describe why you need this loan...') }}"
                        rows="3"
                    />

                    <flux:button type="submit" variant="primary" class="w-full">{{ __('Submit Application') }}</flux:button>
                </form>
            </flux:card>
        </div>

        <div class="lg:col-span-2 space-y-6">
            @if (count($this->previewSchedule) > 0)
                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ __('Schedule Preview') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('This is an estimate based on your current inputs. Final schedule may vary upon approval.') }}
                    </flux:text>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('#') }}</flux:table.column>
                            <flux:table.column>{{ __('Est. Date') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Instalment') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Principal') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Interest') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Balance') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->previewSchedule as $entry)
                                <flux:table.row :key="$entry['instalment_number']">
                                    <flux:table.cell class="text-zinc-500">{{ $entry['instalment_number'] }}</flux:table.cell>
                                    <flux:table.cell>{{ \Carbon\Carbon::parse($entry['due_date'])->format('M j, Y') }}</flux:table.cell>
                                    <flux:table.cell align="end" class="font-medium text-zinc-900 dark:text-white">
                                        {{ Number::currency($entry['instalment_amount']) }}
                                    </flux:table.cell>
                                    <flux:table.cell align="end" class="text-zinc-500">{{ Number::currency($entry['principal_component']) }}</flux:table.cell>
                                    <flux:table.cell align="end" class="text-zinc-500">{{ Number::currency($entry['interest_component']) }}</flux:table.cell>
                                    <flux:table.cell align="end" class="text-zinc-500">{{ Number::currency($entry['outstanding_balance']) }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
                        <flux:text size="sm" class="text-zinc-500">{{ __('Total Repayable:') }}</flux:text>
                        <flux:text size="lg" weight="bold">
                            {{ Number::currency(collect($this->previewSchedule)->sum('instalment_amount')) }}
                        </flux:text>
                    </div>
                </flux:card>
            @else
                <flux:card class="flex flex-col items-center justify-center py-12 text-center border-dashed">
                    <flux:icon icon="heroicon-o-calculator" class="size-12 text-zinc-300 mb-4" />
                    <flux:heading size="lg" class="text-zinc-400">{{ __('No Preview Available') }}</flux:heading>
                    <flux:text class="text-zinc-400">
                        {{ __('Fill in the loan amount and term to see a repayment schedule estimate.') }}
                    </flux:text>
                </flux:card>
            @endif
        </div>
    </div>
</div>
