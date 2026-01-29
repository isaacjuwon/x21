<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            })
            ->with('wallets')
            ->latest()
            ->paginate(10);

        return $this->view(['users' => $users])->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Wallet Management" 
        description="View and manage user wallet balances across the platform."
    />

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="p-6 border-b border-neutral-100 dark:border-neutral-700">
            <x-ui.field class="max-w-xs">
                <x-ui.input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search users by name, email, or phone..." 
                    type="search"
                >
                    <x-slot:prepend>
                        <x-ui.icon name="magnifying-glass" class="w-4 h-4 text-gray-400" />
                    </x-slot:prepend>
                </x-ui.input>
            </x-ui.field>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr class="border-b border-neutral-100 dark:border-neutral-700">
                        <th class="px-6 py-4 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 uppercase tracking-wider">Wallet Balance</th>
                        <th class="px-6 py-4 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar size="sm" :src="$user->avatar_url" circle />
                                    <div class="min-w-0">
                                        <p class="font-bold text-neutral-900 dark:text-white truncate">{{ $user->name }}</p>
                                        <p class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-neutral-900 dark:text-white">
                                        {{ \Illuminate\Support\Number::currency($user->walletBalance) }}
                                    </div>
                                    <div class="flex gap-2">
                                        @foreach($user->wallets as $wallet)
                                            <x-ui.badge color="neutral" class="text-[10px]">
                                                {{ $wallet->type->getLabel() }}: {{ number_format($wallet->balance, 2) }}
                                            </x-ui.badge>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button 
                                    tag="a" 
                                    href="{{ route('admin.wallets.view', $user) }}" 
                                    variant="outline" 
                                    size="sm"
                                >
                                    Manage Wallet
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center">
                                    <x-ui.icon name="wallet" class="w-12 h-12 text-neutral-200 mb-4" />
                                    <p>No users found matching your search.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
