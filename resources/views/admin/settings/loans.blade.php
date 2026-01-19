<?php

use App\Livewire\Concerns\HasToast;
use App\Settings\LoanSettings;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public float $loan_to_shares_ratio;

    public $loanLevels = [];

    public function mount(LoanSettings $settings): void
    {
        $this->loan_to_shares_ratio = $settings->loan_to_shares_ratio;
        $this->loanLevels = \App\Models\LoanLevel::orderBy('maximum_loan_amount')->get()->toArray();
    }

    public function save(LoanSettings $settings)
    {
        $settings->loan_to_shares_ratio = $this->loan_to_shares_ratio;
        $settings->save();

        foreach ($this->loanLevels as $levelData) {
            \App\Models\LoanLevel::find($levelData['id'])->update([
                'repayments_required_for_upgrade' => $levelData['repayments_required_for_upgrade'],
                'interest_rate' => $levelData['interest_rate'],
                'maximum_loan_amount' => $levelData['maximum_loan_amount'],
                'installment_period_months' => $levelData['installment_period_months'],
            ]);
        }

        $this->toastSuccess('Loan settings updated successfully.');
    }

    public function render()
    {
        return $this->view()
            ->title('Loan Settings')
            ->layout('layouts::admin');
    }
};
?>

<section class="w-full">


    <x-layouts.admin.settings heading="Loan Settings" subheading="Configure loan application and management settings">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="loan_to_shares_ratio">Loan to Shares Ratio</x-ui.label>
                    <x-ui.input wire:model="loan_to_shares_ratio" id="loan_to_shares_ratio" type="number" step="0.01" />
                    <x-ui.description>How many times the share value can a user borrow (e.g., 2.0 = 200%)</x-ui.description>
                    <x-ui.error name="loan_to_shares_ratio" />
                </x-ui.field>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Loan Levels Configuration</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Level Name</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Max Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Interest Rate (%)</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Duration (Months)</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Repayments for Upgrade</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($loanLevels as $index => $level)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $level['name'] }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <x-ui.input type="number" wire:model="loanLevels.{{ $index }}.maximum_loan_amount" />
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <x-ui.input type="number" step="0.01" wire:model="loanLevels.{{ $index }}.interest_rate" />
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <x-ui.input type="number" wire:model="loanLevels.{{ $index }}.installment_period_months" />
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <x-ui.input type="number" wire:model="loanLevels.{{ $index }}.repayments_required_for_upgrade" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <x-ui.button variant="primary" type="submit" class="w-full md:w-auto">
                    Save
                </x-ui.button>
            </div>
        </form>
    </x-layouts.admin.settings>
</section>
