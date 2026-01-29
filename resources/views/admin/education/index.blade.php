<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\EducationPlan;

new class extends Component {
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function plans()
    {
        return EducationPlan::query()
            ->with('brand.image')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);
    }

    public function delete(EducationPlan $plan)
    {
        $plan->delete();
        $this->dispatch('toast', message: 'Plan deleted successfully.', type: 'success');
    }

    public function render()
    {
        return $this->view()
            ->title('Education Plans')
            ->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Education Plans</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Manage exam pins</p>
        </div>
        <x-ui.button tag="a" href="{{ route('admin.education.create') }}" variant="primary">
            <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
            New Plan
        </x-ui.button>
    </div>

    <div class="w-full max-w-xs">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search plans..." 
            type="search"
        >
            <x-slot:leading>
                <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-neutral-400" />
            </x-slot:leading>
        </x-ui.input>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr class="border-b border-neutral-100 dark:border-neutral-700">
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Image</th>
                        <th class="px-6 py-4">Provider</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->plans as $plan)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ $plan->name }}
                            </td>
                            <td class="px-6 py-4">
                                @if($plan->brand && $plan->brand->image_url)
                                    <img src="{{ $plan->brand->image_url }}" alt="{{ $plan->brand->name }}" class="w-10 h-10 rounded-[--radius-field] object-cover bg-neutral-50 dark:bg-neutral-800">
                                @else
                                    <div class="w-10 h-10 rounded-[--radius-field] bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400">
                                        <x-ui.icon name="photo" class="w-5 h-5" />
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $plan->brand?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-neutral-900 dark:text-white">
                                {{ number_format($plan->price, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$plan->status ? 'success' : 'neutral'">
                                    {{ $plan->status ? 'Active' : 'Inactive' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button tag="a" href="{{ route('admin.education.view', $plan) }}" variant="ghost" size="sm">
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
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-300 mb-3" />
                                    <p>No plans found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->plans->links() }}
        </div>
    </div>
</div>
