<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function activeLoan()
    {
        return auth()->user()->activeLoan();
    }

    #[Computed]
    public function loans()
    {
        return auth()->user()->loans()
            ->with('loanLevel')
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return $this->view()
            ->title('My Loans')
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-6xl mx-auto p-6">
    <x-page-header 
        heading="My Loans" 
        description="Manage your loan applications and payments"
    >
        <x-slot name="actions">
            <x-ui.button wire:navigate href="/loans/apply">
                Apply for Loan
            </x-ui.button>
        </x-slot>
    </x-page-header>

    @if (session()->has('success'))
        <x-ui.alerts type="success" class="mb-4">
            {{ session('success') }}
        </x-ui.alerts>
    @endif

    <!-- Active Loan -->
    @if ($this->activeLoan)
        <x-ui.card class="mb-6 border-2 border-primary-500">
            <x-slot name="header">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold">Active Loan</h3>
                    <x-ui.badge :color="$this->activeLoan->status_badge">
                        {{ $this->activeLoan->status->getLabel() }}
                    </x-ui.badge>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Loan Amount</p>
                    <p class="text-xl font-bold">{{ Number::currency($this->activeLoan->amount) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Amount Paid</p>
                    <p class="text-xl font-bold text-success-600">{{ Number::currency($this->activeLoan->amount_paid) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Balance Remaining</p>
                    <p class="text-xl font-bold text-warning-600">{{ Number::currency($this->activeLoan->balance_remaining) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Progress</p>
                    <p class="text-xl font-bold">{{ number_format($this->activeLoan->progress_percentage, 1) }}%</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-primary-600 h-3 rounded-full transition-all" style="width: {{ $this->activeLoan->progress_percentage }}%"></div>
                </div>
            </div>

            <div class="flex gap-3">
                <x-ui.button wire:navigate href="/loans/{{ $this->activeLoan->id }}">
                    View Details
                </x-ui.button>
                <x-ui.button variant="outline" wire:navigate href="/loans/{{ $this->activeLoan->id }}/payment">
                    Make Payment
                </x-ui.button>
            </div>
        </x-ui.card>
    @else
        <x-ui.card class="mb-6">
            <div class="text-center py-8">
                <p class="text-gray-600 mb-4">You don't have an active loan</p>
                <x-ui.button wire:navigate href="/loans/apply">
                    Apply for Loan
                </x-ui.button>
            </div>
        </x-ui.card>
    @endif

    <!-- Loan History -->
    <x-ui.card>
        <x-slot name="header">
            <h3 class="text-xl font-bold">Loan History</h3>
        </x-slot>

        @if ($this->loans->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Applied</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($this->loans as $loan)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $loan->applied_at?->format('M d, Y') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    {{ Number::currency($loan->amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $loan->loanLevel->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.badge :color="$loan->status_badge">
                                        {{ $loan->status->getLabel() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ number_format($loan->progress_percentage, 1) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <x-ui.button size="sm" variant="outline" wire:navigate href="/loans/{{ $loan->id }}">
                                        View
                                    </x-ui.button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $this->loans->links() }}
            </div>
        @else
            <div class="text-center py-8 text-gray-600">
                No loan history found
            </div>
        @endif
    </x-ui.card>
</div>