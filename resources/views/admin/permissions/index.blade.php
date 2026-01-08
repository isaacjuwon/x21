<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[On('permission-created')]
    #[On('permission-updated')]
    #[On('permission-deleted')]
    public function refresh()
    {
        // Refresh the component
    }

    #[Computed]
    public function permissions()
    {
        return Permission::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Permissions</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage system permissions</p>
        </div>
        <x-ui.button type="button" x-on:click="$dispatch('show-create-permission-modal')" variant="primary">
            <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
            New Permission
        </x-ui.button>
    </div>

    <div class="w-full max-w-md">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search permissions..." 
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
                        <th class="px-6 py-4">Guard</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->permissions as $permission)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $permission->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $permission->guard_name }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $permission->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button type="button" x-on:click="$dispatch('show-edit-permission-modal', { permissionId: {{ $permission->id }} })" variant="ghost" size="sm">
                                    Edit
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No permissions found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $this->permissions->links() }}
        </div>
    </div>

    <livewire:admin.permissions.create />
    <livewire:admin.permissions.edit />
</div>
