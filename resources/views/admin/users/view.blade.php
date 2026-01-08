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
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400">
                    <x-ui.icon name="wallet" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Wallet Balance</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($user->wallet->balance ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Shares Held -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                    <x-ui.icon name="chart-pie" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Shares</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($user->shares()->sum('quantity')) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Loans Active -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">
                    <x-ui.icon name="banknotes" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Loans</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $user->loans()->where('status', \App\Enums\LoanStatus::ACTIVE)->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Information -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Account Information</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 text-sm">
            <div>
                <dt class="text-gray-500">Full Name</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email Address</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Phone Number</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $user->phone_number ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Joined Date</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y H:i A') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email Verified</dt>
                <dd>
                    <x-ui.badge :color="$user->email_verified_at ? 'success' : 'warning'">
                        {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                    </x-ui.badge>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Roles</dt>
                <dd class="flex flex-wrap gap-2">
                    @forelse($user->roles as $role)
                        <x-ui.badge :color="$role->name === 'super-admin' ? 'danger' : ($role->name === 'admin' ? 'primary' : ($role->name === 'manager' ? 'warning' : 'neutral'))">
                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                        </x-ui.badge>
                    @empty
                        <span class="text-sm text-gray-500">No roles assigned</span>
                    @endforelse
                </dd>
            </div>
        </dl>
    </div>

    <!-- Shares Breakdown -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Share Holdings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">Currency</th>
                        <th class="px-6 py-4">Quantity</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($user->shares as $share)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $share->currency }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ number_format($share->quantity) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$share->status === \App\Enums\ShareStatus::APPROVED ? 'success' : ($share->status === \App\Enums\ShareStatus::PENDING ? 'warning' : 'danger')">
                                    {{ $share->status->name }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No shares found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
