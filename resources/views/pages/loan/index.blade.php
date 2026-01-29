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
        <x-ui.card class="mb-6 border-2 border-primary/20 shadow-lg shadow-primary/5 bg-white dark:bg-neutral-800 rounded-[--radius-box]">
            <x-slot name="header">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Active Loan</h3>
                    <x-ui.badge :color="$this->activeLoan->status_badge" class="text-[10px] font-bold uppercase tracking-widest">
                        {{ $this->activeLoan->status->getLabel() }}
                    </x-ui.badge>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Loan Amount</p>
                    <p class="text-lg font-bold text-neutral-900 dark:text-white">{{ Number::currency($this->activeLoan->amount, 'NGN') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Amount Paid</p>
                    <p class="text-lg font-bold text-success">{{ Number::currency($this->activeLoan->amount_paid, 'NGN') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Remaining</p>
                    <p class="text-lg font-bold text-amber-500">{{ Number::currency($this->activeLoan->balance_remaining, 'NGN') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Progress</p>
                    <p class="text-lg font-bold text-neutral-900 dark:text-white">{{ number_format($this->activeLoan->progress_percentage, 1) }}%</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="w-full bg-neutral-100 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                    <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ $this->activeLoan->progress_percentage }}%"></div>
                </div>
            </div>

            <div class="flex gap-4">
                <x-ui.button wire:navigate href="/loans/{{ $this->activeLoan->id }}" class="flex-1 h-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">
                    View Details
                </x-ui.button>
                <x-ui.button variant="outline" wire:navigate href="/loans/{{ $this->activeLoan->id }}/payment" class="flex-1 h-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">
                    Make Payment
                </x-ui.button>
            </div>
        </x-ui.card>
    @else
        <x-ui.card class="mb-6 bg-neutral-50 dark:bg-neutral-900/50 border-neutral-100 dark:border-neutral-700 shadow-none rounded-[--radius-box]">
            <div class="text-center py-10">
                <p class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-6">You don't have an active loan</p>
                <x-ui.button wire:navigate href="/loans/apply" class="h-12 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">
                    Apply for Loan
                </x-ui.button>
            </div>
        </x-ui.card>
    @endif

    <!-- Loan History -->
    <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none">
        <x-slot name="header">
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Loan History</h3>
        </x-slot>

        @if ($this->loans->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y border border-neutral-100 dark:border-neutral-700 divide-neutral-100 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-900/50 text-neutral-500 dark:text-neutral-400 uppercase tracking-widest text-[10px] font-bold">
                        <tr>
                            <th class="px-6 py-4 text-left">Date Applied</th>
                            <th class="px-6 py-4 text-left">Amount</th>
                            <th class="px-6 py-4 text-left">Level</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Progress</th>
                            <th class="px-6 py-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-100 dark:divide-neutral-700">
                        @foreach ($this->loans as $loan)
                            <tr>
                                <td class="px-6 py-5 whitespace-nowrap text-xs text-neutral-500 dark:text-neutral-400 font-bold">
                                    {{ $loan->applied_at?->format('M d, Y') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-xs font-bold text-neutral-900 dark:text-white">
                                    {{ Number::currency($loan->amount, 'NGN') }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">
                                    {{ $loan->loanLevel?->name ?? 'Default' }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <x-ui.badge :color="$loan->status_badge" class="text-[10px] font-bold uppercase tracking-widest">
                                        {{ $loan->status->getLabel() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-xs font-bold text-neutral-900 dark:text-white">
                                    {{ number_format($loan->progress_percentage, 1) }}%
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <x-ui.button size="xs" variant="outline" wire:navigate href="/loans/{{ $loan->id }}" class="rounded-[--radius-field] font-bold uppercase tracking-widest">
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
            <div class="text-center py-10 bg-neutral-50/50 dark:bg-neutral-900/20 rounded-[--radius-box] border-2 border-dashed border-neutral-100 dark:border-neutral-700">
                <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-200 dark:text-neutral-700 mx-auto mb-4" />
                <p class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">No loan history found</p>
            </div>
        @endif
    </x-ui.card>
</div>