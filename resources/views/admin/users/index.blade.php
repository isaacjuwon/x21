<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[On('user-created')]
    #[On('user-updated')]
    #[On('user-deleted')]
    public function refresh()
    {
        // Refresh the component
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
                ->orWhere('phone_number', 'like', '%'.$this->search.'%'))
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage platform users</p>
        </div>
        <x-ui.button icon="plus" type="button" x-on:click="$dispatch('show-create-user-modal')" variant="primary">
            New User
        </x-ui.button>
    </div>

    <div class="w-full max-w-md">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search name or email..." 
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
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                        {{ $user->initials() }}
                                    </div>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button tag="a" href="{{ route('admin.users.view', $user) }}" variant="ghost" size="sm">
                                        View
                                    </x-ui.button>
                                    <x-ui.button type="button" x-on:click="$dispatch('show-edit-user-modal', { userId: {{ $user->id }} })" variant="ghost" size="sm">
                                        Edit
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No users found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $this->users->links() }}
        </div>
    </div>

    <livewire:admin::users.create />
    <livewire:admin::users.edit />
</div>
