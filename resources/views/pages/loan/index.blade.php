<?php

use App\Models\Loan;
use App\Enums\Loans\LoanStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Loans'), Defer] class extends Component {
    use WithPagination;

    #[Url(as: 'page')]
    public $page = 1;

    #[Url]
    public $sortBy = 'created_at';

    #[Url]
    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function loans()
    {
        return Auth::user()->loans()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function activeLoansCount(): int
    {
        return Auth::user()->loans()
            ->whereIn('status', [LoanStatus::Active, LoanStatus::Disbursed])
            ->count();
    }

    #[Computed]
    public function totalOutstandingBalance(): float
    {
        return (float) Auth::user()->loans()
            ->where('status', LoanStatus::Disbursed)
            ->sum('outstanding_balance');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="space-y-6 animate-pulse">
            <div class="flex items-center justify-between">
                <div class="h-8 w-32 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                <div class="h-10 w-36 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
            </div>
            <div class="h-64 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('My Loans') }}</flux:heading>

        <flux:button href="{{ route('loan.apply') }}" variant="primary" icon="plus">
            {{ __('Apply for Loan') }}
        </flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Active Loans') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ $this->activeLoansCount }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Total Outstanding') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ Number::currency($this->totalOutstandingBalance) }}
            </flux:text>
        </flux:card>
    </div>

    <flux:card class="p-2 overflow-hidden border-zinc-200 dark:border-zinc-800">
        <flux:table :paginate="$this->loans">
            <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('Date Applied') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'principal_amount'" :direction="$sortDirection" wire:click="sort('principal_amount')">{{ __('Amount') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'interest_rate'" :direction="$sortDirection" wire:click="sort('interest_rate')">{{ __('Rate') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'repayment_term_months'" :direction="$sortDirection" wire:click="sort('repayment_term_months')">{{ __('Term') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'outstanding_balance'" :direction="$sortDirection" wire:click="sort('outstanding_balance')">{{ __('Outstanding') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">{{ __('Status') }}</flux:table.column>
                <flux:table.column align="end"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->loans as $loan)
                    <flux:table.row :key="$loan->id">
                        <flux:table.cell class="text-zinc-500 whitespace-nowrap">
                            {{ $loan->created_at->format('M j, Y') }}
                        </flux:table.cell>

                        <flux:table.cell class="font-medium">
                            {{ Number::currency($loan->principal_amount) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ number_format($loan->interest_rate, 2) }}%
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $loan->repayment_term_months }} {{ str('month')->plural($loan->repayment_term_months) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ Number::currency($loan->outstanding_balance ?? 0) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge :color="$loan->status->getFluxColor()" :icon="$loan->status->getFluxIcon()" size="sm" variant="subtle">
                                {{ $loan->status->getLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <flux:button :href="route('loan.view', $loan)" size="sm" variant="ghost" icon="eye" inset="top bottom" />
                        </flux:table.cell>
                    </flux:row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
