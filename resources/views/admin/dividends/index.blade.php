<?php

use App\Models\Dividend;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function dividends()
    {
        return Dividend::latest()->paginate(10);
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Dividend Declarations</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Manage share dividend payouts</p>
        </div>
        <x-ui.button tag="a" href="{{ route('admin.dividends.create') }}" variant="primary">
            <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
            Declare Dividend
        </x-ui.button>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr>
                        <th class="px-6 py-4">Declaration Date</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Payment Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->dividends as $dividend)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 text-neutral-900 dark:text-white font-bold">
                                {{ $dividend->declaration_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="capitalize">{{ $dividend->type }}</span>
                            </td>
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ Number::currency($dividend->amount_per_share) }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $dividend->payment_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$dividend->paid_out ? 'success' : 'warning'" class="text-[10px]">
                                    {{ $dividend->paid_out ? 'Paid Out' : 'Pending' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.dividends.view', $dividend) }}" class="text-primary hover:text-primary-600 font-bold">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400 font-bold">
                                No dividends found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->dividends->links() }}
        </div>
    </div>
</div>
