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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dividends</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage dividend declarations</p>
        </div>
        <x-ui.button icon="plus" tag="a" href="{{ route('admin.dividends.create') }}" variant="primary">
            Declare Dividend
        </x-ui.button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount/Share</th>
                        <th class="px-6 py-4">Currency</th>
                        <th class="px-6 py-4">Declaration Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->dividends as $dividend)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ ucfirst($dividend->type) }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ number_format($dividend->amount_per_share, 4) }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $dividend->currency }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $dividend->declaration_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$dividend->paid_out ? 'success' : 'warning'">
                                    {{ $dividend->paid_out ? 'Paid Out' : 'Pending' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button tag="a" href="{{ route('admin.dividends.view', $dividend) }}" variant="ghost" size="sm">
                                    View
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No dividends found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $this->dividends->links() }}
        </div>
    </div>
</div>
