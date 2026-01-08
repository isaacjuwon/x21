<?php

use App\Models\Brand;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[On('refresh-brand-list')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return $this->view()
            ->title('Brands')
            ->layout('layouts::admin');
    }

    #[Computed]
    public function brands()
    {
        return Brand::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);
    }

    
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Brands</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your service brands</p>
        </div>
        <x-ui.modal.trigger id="create-brand">
            <x-ui.button type="button" icon="plus" variant="primary">
                New Brand
            </x-ui.button>
        </x-ui.modal.trigger>
    </div>

    <div class="flex items-center gap-4">
        <div class="w-full max-w-md">
            <x-ui.input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search brands..." 
                type="search"
            >
                <x-slot:leading>
                    <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
                </x-slot:leading>
            </x-ui.input>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">Image</th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->brands as $brand)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                @if($brand->image_url)
                                    <img src="{{ $brand->image_url }}" alt="{{ $brand->name }}" class="h-10 w-10 object-contain rounded-lg border border-gray-200 dark:border-gray-700">
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                        <x-ui.icon name="photo" class="w-5 h-5" />
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $brand->name }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$brand->status ? 'success' : 'danger'">
                                    {{ $brand->status ? 'Active' : 'Inactive' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                    <x-ui.button variant="ghost" @click="$dispatch('show-view-brand', {id: {{ $brand->id }}})" size="sm">
                                        View
                                    </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No brands found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $this->brands->links() }}
        </div>
    </div>

    <livewire:admin::brands.create />
    <livewire:admin::brands.view />
</div>
