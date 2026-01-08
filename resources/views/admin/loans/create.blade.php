<?php

use App\Enums\LoanStatus;
use App\Livewire\Concerns\HasToast;
use App\Models\Loan;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    #[Rule('required|exists:users,id')]
    public ?int $user_id = null;

    #[Rule('required|numeric|min:100')]
    public float $amount = 0;

    #[Rule('required|integer|min:1')]
    public int $installment_months = 1;

    #[Rule('required|numeric|min:0')]
    public float $interest_rate = 5.0; // Default 5%

    public function save()
    {
        $this->validate();

        // Simple calculation logic for demo purposes
        $totalRepayment = $this->amount * (1 + ($this->interest_rate / 100));
        $monthlyPayment = $totalRepayment / $this->installment_months;

        Loan::create([
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'interest_rate' => $this->interest_rate,
            'installment_months' => $this->installment_months,
            'monthly_payment' => $monthlyPayment,
            'total_repayment' => $totalRepayment,
            'amount_paid' => 0,
            'balance_remaining' => $totalRepayment,
            'status' => LoanStatus::PENDING,
            'applied_at' => now(),
        ]);

        $this->toastSuccess('Loan created successfully.');

        return redirect()->route('admin.loans.index');
    }

    public function render()
    {
        return $this->view()->with([
            'users' => User::orderBy('name')->get(),
        ])->layout('layouts::admin');
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Create Loan" 
        description="Record a new loan application"
    >
        <x-slot:actions>
            <x-ui.button tag="a" href="{{ route('admin.loans.index') }}" variant="outline">
                Cancel
            </x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="user_id">User</x-ui.label>
                <x-ui.select wire:model="user_id" id="user_id">
                    <x-ui.select.option value="">Select a user</x-ui.select.option>
                    @foreach($users as $user)
                        <x-ui.select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</x-ui.select.option>
                    @endforeach
                </x-ui.select>
                <x-ui.error name="user_id" />
            </x-ui.field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="amount">Amount</x-ui.label>
                    <x-ui.input wire:model="amount" id="amount" type="number" step="0.01" />
                    <x-ui.error name="amount" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="interest_rate">Interest Rate (%)</x-ui.label>
                    <x-ui.input wire:model="interest_rate" id="interest_rate" type="number" step="0.1" />
                    <x-ui.error name="interest_rate" />
                </x-ui.field>
            </div>

            <x-ui.field>
                <x-ui.label for="installment_months">Duration (Months)</x-ui.label>
                <x-ui.input wire:model="installment_months" id="installment_months" type="number" min="1" />
                <x-ui.error name="installment_months" />
            </x-ui.field>

            <div class="flex justify-end pt-4">
                <x-ui.button type="submit" variant="primary">
                    Create Loan
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
