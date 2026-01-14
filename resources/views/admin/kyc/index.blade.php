<?php

use App\Models\KycVerification;
use App\Enums\Kyc\Status as KycStatusEnum;
use App\Enums\Kyc\VerificationMode;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterMode = '';

    #[Computed]
    public function verifications()
    {
        return KycVerification::query()
            ->with('user')
            ->when($this->search, fn ($q) => 
                $q->where('id_number', 'like', '%'.$this->search.'%')
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            )
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterMode, fn ($q) => $q->where('verification_mode', $this->filterMode))
            ->latest()
            ->paginate(15);
    }

    public function delete(KycVerification $verification)
    {
        $verification->delete();
        $this->dispatch('toast', message: 'KYC record deleted successfully.', type: 'success');
    }

    public function render()
    {
        return $this->view()
            ->title('KYC Verifications')
            ->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">KYC Verifications</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage user KYC verification records</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by ID number or user..." 
            type="search"
        >
            <x-slot:leading>
                <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
            </x-slot:leading>
        </x-ui.input>

        <x-ui.select wire:model.live="filterStatus">
            <x-ui.select.option value="">All Status</x-ui.select.option>
            <x-ui.select.option value="pending">Pending</x-ui.select.option>
            <x-ui.select.option value="verified">Verified</x-ui.select.option>
            <x-ui.select.option value="failed">Failed</x-ui.select.option>
        </x-ui.select>

        <x-ui.select wire:model.live="filterMode">
            <x-ui.select.option value="">All Modes</x-ui.select.option>
            <x-ui.select.option value="automatic">Automatic</x-ui.select.option>
            <x-ui.select.option value="manual">Manual</x-ui.select.option>
        </x-ui.select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">ID Number</th>
                        <th class="px-6 py-4">Mode</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->verifications as $verification)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <a href="{{ route('admin.users.view', $verification->user) }}" class="text-blue-600 hover:text-blue-700">
                                    {{ $verification->user->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    {{ strtoupper($verification->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-500 dark:text-gray-400">
                                {{ substr($verification->id_number, 0, 4) }}****{{ substr($verification->id_number, -4) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$verification->verification_mode === VerificationMode::Automatic ? 'info' : 'warning'">
                                    {{ ucfirst($verification->verification_mode->value) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$verification->status === KycStatusEnum::Verified ? 'success' : ($verification->status === KycStatusEnum::Failed ? 'danger' : 'warning')">
                                    {{ ucfirst($verification->status->value) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $verification->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.kyc.view', $verification) }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center">
                                <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                <p class="text-gray-500 dark:text-gray-400">No KYC verifications found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($this->verifications->count() > 0)
        <div class="flex justify-center">
            {{ $this->verifications->links() }}
        </div>
    @endif
</div>
