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
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">KYC Verifications</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Manage user KYC verification records</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by ID number or user..." 
            type="search"
        >
            <x-slot:leading>
                <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-neutral-400" />
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

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr class="border-b border-neutral-100 dark:border-neutral-700">
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">ID Number</th>
                        <th class="px-6 py-4">Mode</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->verifications as $verification)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                <a href="{{ route('admin.users.view', $verification->user) }}" class="text-primary hover:text-primary-600">
                                    {{ $verification->user->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">
                                    {{ strtoupper($verification->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-neutral-500 dark:text-neutral-400">
                                {{ substr($verification->id_number, 0, 4) }}****{{ substr($verification->id_number, -4) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$verification->verification_mode === VerificationMode::Automatic ? 'info' : 'warning'" class="text-[10px]">
                                    {{ ucfirst($verification->verification_mode->value) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$verification->status === KycStatusEnum::Verified ? 'success' : ($verification->status === KycStatusEnum::Failed ? 'danger' : 'warning')" class="text-[10px]">
                                    {{ ucfirst($verification->status->value) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $verification->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.kyc.view', $verification) }}" class="text-primary hover:text-primary-600 font-bold text-xs">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center">
                                <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-300 mx-auto mb-3" />
                                <p class="text-neutral-500 dark:text-neutral-400">No KYC verifications found</p>
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
