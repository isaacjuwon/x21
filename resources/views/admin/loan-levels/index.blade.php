<?php

use App\Models\LoanLevel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function loanLevels()
    {
        return LoanLevel::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->withCount('users')
            ->latest()
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Loan Levels</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Manage loan levels and eligibility tiers</p>
        </div>
        <x-ui.button tag="a" icon="plus" href="{{ route('admin.loan-levels.create') }}" variant="primary">
            New Loan Level
        </x-ui.button>
    </div>

    <x-ui.card>
        <div class="p-6 space-y-4">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                placeholder="Search loan levels..."
                type="search"
            >
                <x-slot:leading>
                <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-neutral-400" />
                </x-slot:leading>
            </x-ui.input>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Name</th>
                            <th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Max Amount</th>
                            <th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Interest Rate</th>
                            <th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Period</th>
                            <th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Users</th>
                            <th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-right text-[10px] uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($this->loanLevels as $level)
                            <tr wire:key="level-{{ $level->id }}" class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold text-neutral-900 dark:text-white">{{ $level->name }}</div>
                                    <div class="text-[10px] text-neutral-500 dark:text-neutral-400">{{ $level->slug }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-neutral-900 dark:text-white">
                                    {{ Number::currency($level->maximum_loan_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-900 dark:text-white">
                                    {{ $level->interest_rate }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-900 dark:text-white">
                                    {{ $level->installment_period_months }} months
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-900 dark:text-white">
                                    {{ $level->users_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($level->is_active)
                                        <span class="px-2 inline-flex text-[10px] leading-5 font-bold rounded-full bg-success/10 text-success uppercase tracking-widest">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-[10px] leading-5 font-bold rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300 uppercase tracking-widest">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                                    <x-ui.button tag="a" href="{{ route('admin.loan-levels.edit', $level) }}" variant="ghost" size="sm">
                                        Edit
                                    </x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-300 mb-3" />
                                        <p>No loan levels found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $this->loanLevels->links() }}
            </div>
        </div>
    </x-ui.card>
</div>
