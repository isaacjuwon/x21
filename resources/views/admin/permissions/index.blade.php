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
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Permissions</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Manage system permissions and access control</p>
        </div>
        <x-ui.button type="button" x-on:click="$dispatch('show-create-permission-modal')" variant="primary">
            <x-ui.icon name="plus" class="w-4 h-4 mr-2" />
            New Permission
        </x-ui.button>
    </div>

    <div class="w-full max-w-xs">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search permissions..." 
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
                    <tr>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Guard</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->permissions as $permission)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                {{ $permission->name }}
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400 font-mono text-xs">
                                {{ $permission->guard_name }}
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $permission->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button type="button" x-on:click="$dispatch('show-edit-permission-modal', { permissionId: {{ $permission->id }} })" variant="ghost" size="sm" class="font-bold">
                                    Edit
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-300 mb-3" />
                                    <p>No permissions found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->permissions->links() }}
        </div>
    </div>

    <livewire:admin.permissions.create />
    <livewire:admin.permissions.edit />
</div>
