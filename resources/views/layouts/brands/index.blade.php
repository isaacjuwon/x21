<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Brand;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function with(): array
    {
        return [
            'brands' => Brand::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Brands</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your service brands</p>
        </div>
        <x-ui.button tag="a" href="{{ route('admin.brands.create') }}" variant="primary">
            <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
            New Brand
        </x-ui.button>
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
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">API Code</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $brand->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    {{ $brand->api_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$brand->status ? 'success' : 'danger'">
                                    {{ $brand->status ? 'Active' : 'Inactive' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button tag="a" href="{{ route('admin.brands.view', $brand) }}" variant="ghost" size="sm">
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
            {{ $brands->links() }}
        </div>
    </div>
</div>
