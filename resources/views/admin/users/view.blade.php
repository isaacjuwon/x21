<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Livewire\Concerns\HasToast;

new class extends Component {
    use HasToast;

    public User $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function impersonate()
    {
        // Dummy implementation as requested
        $this->toastSuccess("Impersonating {$this->user->name}...");
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-4xl mx-auto p-6 space-y-6">
    <x-page-header 
        :heading="$user->name" 
        :description="$user->email"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <x-ui.button 
                    type="button" 
                    wire:click="impersonate" 
                    variant="neutral" 
                    outline
                >
                    <x-ui.icon name="user-circle" class="w-4 h-4 mr-2" />
                    Impersonate
                </x-ui.button>
                <x-ui.button tag="a" href="{{ route('admin.users.index') }}" variant="outline">
                    Back
                </x-ui.button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Wallet Balance -->
        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-[--radius-field] bg-primary/20 text-primary">
                    <x-ui.icon name="wallet" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Wallet Balance</h3>
                    <p class="text-xl font-bold text-neutral-900 dark:text-white">
                        {{ number_format($user->wallet->balance ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Shares Held -->
        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-[--radius-field] bg-secondary/20 text-secondary">
                    <x-ui.icon name="chart-pie" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Total Shares</h3>
                    <p class="text-xl font-bold text-neutral-900 dark:text-white">
                        {{ number_format($user->shares()->sum('quantity')) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Loans Active -->
        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-[--radius-field] bg-warning/20 text-warning">
                    <x-ui.icon name="banknotes" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Active Loans</h3>
                    <p class="text-xl font-bold text-neutral-900 dark:text-white">
                        {{ $user->loans()->where('status', \App\Enums\LoanStatus::ACTIVE)->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Information -->
    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
        <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-6">Account Information</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 text-sm">
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Full Name</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $user->name }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Email Address</dt>
                <dd class="font-bold text-neutral-900 dark:text-white text-xs">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Phone Number</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $user->phone_number ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Joined Date</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $user->created_at->format('M d, Y H:i A') }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Email Verified</dt>
                <dd>
                    <x-ui.badge :color="$user->email_verified_at ? 'success' : 'warning'">
                        {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                    </x-ui.badge>
                </dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Roles</dt>
                <dd class="flex flex-wrap gap-2">
                    @forelse($user->roles as $role)
                        <x-ui.badge :color="$role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'primary' : ($role->name === 'manager' ? 'warning' : 'neutral'))" class="text-[10px]">
                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                        </x-ui.badge>
                    @empty
                        <span class="text-sm text-neutral-500">No roles assigned</span>
                    @endforelse
                </dd>
            </div>
        </dl>
    </div>

    <!-- Shares Breakdown -->
    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Share Holdings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr>
                        <th class="px-6 py-4 text-[10px] uppercase tracking-widest">Currency</th>
                        <th class="px-6 py-4 text-[10px] uppercase tracking-widest">Quantity</th>
                        <th class="px-6 py-4 text-[10px] uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($user->shares as $share)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ $share->currency }}
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400 font-bold">
                                {{ number_format($share->quantity) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$share->status === \App\Enums\ShareStatus::APPROVED ? 'success' : ($share->status === \App\Enums\ShareStatus::PENDING ? 'warning' : 'danger')" class="text-[10px]">
                                    {{ $share->status->name }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                                No shares found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
