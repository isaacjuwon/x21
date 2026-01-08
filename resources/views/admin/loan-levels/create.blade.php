<?php

use App\Livewire\Concerns\HasToast;
use App\Models\LoanLevel;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public string $name = '';
    public string $slug = '';
    public string $maximum_loan_amount = '';
    public int $installment_period_months = 12;
    public string $interest_rate = '';
    public bool $is_active = true;

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:loan_levels,slug',
            'maximum_loan_amount' => 'required|numeric|min:0',
            'installment_period_months' => 'required|integer|min:1|max:120',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        LoanLevel::create($validated);

        $this->toastSuccess('Loan level created successfully.');

        return redirect()->route('admin.loan-levels.index');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-4xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Loan Level</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a new loan eligibility tier</p>
    </div>

    <x-ui.card>
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="name">Name</x-ui.label>
                    <x-ui.input wire:model.live="name" id="name" placeholder="e.g., Bronze, Silver, Gold" />
                    <x-ui.error name="name" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="slug">Slug</x-ui.label>
                    <x-ui.input wire:model="slug" id="slug" placeholder="auto-generated" />
                    <x-ui.error name="slug" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="maximum_loan_amount">Maximum Loan Amount</x-ui.label>
                    <x-ui.input wire:model="maximum_loan_amount" id="maximum_loan_amount" type="number" step="0.01" placeholder="0.00" />
                    <x-ui.error name="maximum_loan_amount" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="installment_period_months">Installment Period (Months)</x-ui.label>
                    <x-ui.input wire:model="installment_period_months" id="installment_period_months" type="number" min="1" max="120" />
                    <x-ui.error name="installment_period_months" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="interest_rate">Interest Rate (%)</x-ui.label>
                    <x-ui.input wire:model="interest_rate" id="interest_rate" type="number" step="0.01" placeholder="0.00" />
                    <x-ui.error name="interest_rate" />
                </x-ui.field>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.checkbox wire:model="is_active" id="is_active" />
                <x-ui.label for="is_active" class="mb-0">Active</x-ui.label>
            </div>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button tag="a" href="{{ route('admin.loan-levels.index') }}" variant="ghost">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    Create Loan Level
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
