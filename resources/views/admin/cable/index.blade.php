<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\CablePlan;

new class extends Component {
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function plans()
    {
        return CablePlan::query()
            ->with('brand.image')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);
    }

    public function delete(CablePlan $plan)
    {
        $plan->delete();
        $this->dispatch('toast', message: 'Plan deleted successfully.', type: 'success');
    }

    public function render()
    {
        return $this->view()
            ->title('Cable Plans')
            ->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cable Plans</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage cable TV packages</p>
        </div>
        <x-ui.button tag="a" href="{{ route('admin.cable.create') }}" variant="primary">
            <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
            New Plan
        </x-ui.button>
    </div>

    <div class="w-full max-w-md">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search plans..." 
            type="search"
        >
            <x-slot:leading>
                <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
            </x-slot:leading>
        </x-ui.input>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Image</th>
                        <th class="px-6 py-4">Provider</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->plans as $plan)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $plan->name }}
                            </td>
                            <td class="px-6 py-4">
                                @if($plan->brand && $plan->brand->image_url)
                                    <img src="{{ $plan->brand->image_url }}" alt="{{ $plan->brand->name }}" class="w-10 h-10 rounded-lg object-cover bg-gray-50 dark:bg-gray-800">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                        <x-ui.icon name="photo" class="w-5 h-5" />
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $plan->brand?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-900 dark:text-white">
                                {{ number_format($plan->price, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$plan->status ? 'success' : 'neutral'">
                                    {{ $plan->status ? 'Active' : 'Inactive' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button tag="a" href="{{ route('admin.cable.view', $plan) }}" variant="ghost" size="sm">
                                        View
                                    </x-ui.button>
                                    <x-ui.button 
                                        wire:click="delete({{ $plan->id }})" 
                                        wire:confirm="Are you sure you want to delete this plan?"
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        Delete
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No plans found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $this->plans->links() }}
        </div>
    </div>
</div>
